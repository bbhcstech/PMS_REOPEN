<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\{DB, Schema};
use App\Http\Controllers\OrganizationDirectoryController;
use Illuminate\Http\Request;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
Schema::connection('tenant')->create('users', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('role'); $t->timestamp('archived_at')->nullable();
    $t->boolean('is_active')->default(true); $t->boolean('login_allowed')->default(true); $t->timestamps();
});
Schema::connection('tenant')->create('employee_details', function ($t) { $t->id(); $t->integer('user_id'); $t->integer('reporting_to')->nullable(); });
foreach ([1, 2, 3, 4, 5, 6] as $id) {
    DB::connection('tenant')->table('users')->insert(['id' => $id, 'company_id' => $id === 5 ? 2 : 1, 'role' => $id === 6 ? 'admin' : 'employee']);
}
$actor = new class extends App\Models\User {
    public bool $allowed = true;
    public function normalizedRole(): string { return $this->role ?? 'admin'; }
    public function hasModulePermission(string $slug, string $permission = 'view'): bool { return $this->allowed; }
};
$actor->forceFill(['id' => 10, 'company_id' => 1, 'role' => 'admin']);
$delete = function ($ids) use ($actor) {
    $request = Request::create('/organization/employees', 'DELETE', ['employee_ids' => $ids]);
    $request->setUserResolver(fn () => $actor);
    return (new OrganizationDirectoryController())->deleteEmployees($request);
};
function deletionCheck($ok, $message) { if (! $ok) throw new RuntimeException($message); }
function expectDeleteFailure($call, $status) {
    try { $call(); } catch (Illuminate\Validation\ValidationException $e) { deletionCheck($status === 422, 'Wrong validation result.'); return; }
    catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { deletionCheck($e->getStatusCode() === $status, 'Wrong authorization result.'); return; }
    throw new RuntimeException('Unsafe deletion accepted.');
}
$delete([1]);
$one = DB::connection('tenant')->table('users')->find(1);
deletionCheck($one->archived_at && ! $one->is_active && ! $one->login_allowed, 'Single delete did not archive and disable login.');
$delete([2, 3]);
deletionCheck(DB::connection('tenant')->table('users')->whereNotNull('archived_at')->count() === 3, 'Bulk delete failed.');
expectDeleteFailure(fn () => $delete([4, 5]), 403);
expectDeleteFailure(fn () => $delete([6]), 403);
expectDeleteFailure(fn () => $delete([]), 422);
expectDeleteFailure(fn () => $delete([4, 4]), 422);
deletionCheck(DB::connection('tenant')->table('users')->find(4)->archived_at === null, 'Invalid selection partially deleted.');
DB::connection('tenant')->table('employee_details')->insert(['user_id' => 3, 'reporting_to' => 4]);
expectDeleteFailure(fn () => $delete([4]), 422);
$actor->allowed = false;
expectDeleteFailure(fn () => $delete([4]), 403);
$actor->allowed = true; $actor->role = 'employee';
expectDeleteFailure(fn () => $delete([4]), 403);
echo "Directory deletion: single, bulk, login disabling, company isolation, permissions, validation and manager protection passed.\n";
