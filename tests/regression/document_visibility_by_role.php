<?php

// Run with: php tests/regression/document_visibility_by_role.php
// My Documents: company Admin (and platform Super Admin) see every role's documents; HR, Manager,
// Associate Manager (company staff role) and Employee only ever see / open their own.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\UserDocumentController;
use App\Models\User;
use Illuminate\Support\Facades\{Auth, DB};
use Symfony\Component\HttpKernel\Exception\HttpException;

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant', 'cache.default' => 'array', 'session.driver' => 'array']);
DB::statement('CREATE TABLE users (id integer primary key, company_id integer, name text, email text, role text, company_staff_role_id integer, created_at text, updated_at text)');
foreach (['employee_documents', 'hr_documents', 'manager_documents', 'admin_documents'] as $table) {
    DB::statement("CREATE TABLE {$table} (id integer primary key, user_id integer, document_type text, file_path text, file_name text, file_size integer, file_type text, status text, rejection_reason text, uploaded_at text, created_at text, updated_at text)");
}
DB::statement('CREATE TABLE document_views (id integer primary key, document_table text, document_id integer, viewed_by_user_id integer, viewed_at text, created_at text, updated_at text)');
DB::statement('CREATE TABLE app_settings (id integer primary key, key text, value text, created_at text, updated_at text)');
$app->instance(App\Services\CompanyContext::class, new class extends App\Services\CompanyContext {
    public function __construct() {}
    public function current() { return null; }
    public function id(): ?int { return 3; }
});
function check(bool $ok, string $message): void { if (! $ok) throw new RuntimeException($message); }

$people = [
    1 => ['admin', 'admin_documents'], 2 => ['hr', 'hr_documents'], 3 => ['manager', 'manager_documents'],
    4 => ['employee', 'employee_documents'], 5 => ['manager', 'manager_documents'], 6 => ['employee', 'employee_documents'],
];
foreach ($people as $id => [$role, $table]) {
    // User 5 is an "Associate Manager" (company staff role); user 6 belongs to another company.
    DB::table('users')->insert(['id' => $id, 'company_id' => $id === 6 ? 9 : 3, 'name' => "User {$id}", 'email' => "u{$id}@x.test", 'role' => $role, 'company_staff_role_id' => $id === 5 ? 1 : null]);
    DB::table($table)->insert(['id' => $id * 10, 'user_id' => $id, 'document_type' => "Doc {$id}", 'file_path' => 'x', 'file_name' => "doc{$id}.pdf", 'created_at' => now()]);
}
$typeOf = fn (string $table) => explode('_', $table)[0];
$controller = new UserDocumentController;
$view = function (int $id) use ($controller) {
    Auth::guard('web')->setUser(User::find($id));
    return $controller->index()->getData();
};
$canOpen = function (int $viewer, int $owner) use ($controller, $people, $typeOf) {
    Auth::guard('web')->setUser(User::find($viewer));
    try {
        $controller->history($typeOf($people[$owner][1]), $owner * 10);
        return true;
    } catch (HttpException $e) {
        if ($e->getStatusCode() !== 403) throw $e;
        return false;
    }
};

// Admin: repository of every role in their company (not the other company), and can open them.
$admin = $view(1);
check($admin['canViewAllDocuments'], 'Admin cannot see all documents.');
check($admin['repositoryDocs']->pluck('user_id')->sort()->values()->all() === [1, 2, 3, 4, 5], 'Admin repository wrong: ' . $admin['repositoryDocs']->pluck('user_id')->implode(','));
foreach ([2, 3, 4, 5] as $owner) check($canOpen(1, $owner), "Admin cannot open document of user {$owner}.");
check(! $canOpen(1, 6), 'Admin opened another company\'s document.');

// Everyone else: no repository, only their own documents, and every other document is refused.
foreach ([2 => 'HR', 3 => 'Manager', 4 => 'Employee', 5 => 'Associate Manager'] as $id => $label) {
    $data = $view($id);
    check(! $data['canViewAllDocuments'] && $data['repositoryDocs']->isEmpty(), "{$label} sees the document repository.");
    check($data['myDocs']->pluck('user_id')->unique()->values()->all() === [$id], "{$label} My Documents is not only their own.");
    check($canOpen($id, $id), "{$label} cannot open own document.");
    foreach (array_diff(array_keys($people), [$id]) as $other) {
        check(! $canOpen($id, $other), "{$label} opened document of user {$other}.");
    }
}

foreach (['user-documents.index'] as $template) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(view()->getFinder()->find($template))), TOKEN_PARSE);
}
echo "PASS: Admin sees and opens every role's documents in its company only; HR, Manager, Associate Manager and Employee see and open strictly their own.\n";
