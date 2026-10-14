<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DesignationController;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'database.default' => 'tenant', 'session.driver' => 'array']);
DB::purge('tenant');
Schema::create('users', function ($t) { $t->id(); $t->unsignedBigInteger('company_id'); });
Schema::create('designations', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('added_by')->nullable(); $t->unsignedBigInteger('parent_id')->nullable(); $t->timestamp('archived_at')->nullable();
});
Schema::create('employee_details', function ($t) { $t->id(); $t->unsignedBigInteger('designation_id'); });
DB::table('designations')->insert([
    ['id' => 1, 'company_id' => 1, 'parent_id' => null, 'archived_at' => now()],
    ['id' => 2, 'company_id' => 1, 'parent_id' => null, 'archived_at' => now()],
    ['id' => 3, 'company_id' => 1, 'parent_id' => null, 'archived_at' => null],
    ['id' => 4, 'company_id' => 2, 'parent_id' => null, 'archived_at' => now()],
    ['id' => 5, 'company_id' => 1, 'parent_id' => 2, 'archived_at' => now()],
    ['id' => 6, 'company_id' => 1, 'parent_id' => null, 'archived_at' => now()],
]);
DB::table('employee_details')->insert(['designation_id' => 6]);
$actor = new class extends \App\Models\User {
    public bool $allowed = true;
    public function hasModulePermission(string $moduleSlug, string $permission = 'view'): bool { return $this->allowed; }
};
$actor->forceFill(['id' => 1, 'company_id' => 1, 'role' => 'admin']);
Auth::guard('web')->setUser($actor);
$app['session']->start();
$controller = new DesignationController;
$controller->deleteArchived(1);
if (DB::table('designations')->where('id', 1)->exists()) throw new RuntimeException('Archived record was not deleted.');
foreach ([2, 6] as $id) {
    $controller->deleteArchived($id);
    if (!DB::table('designations')->where('id', $id)->exists()) throw new RuntimeException('Linked record was deleted.');
}
foreach ([3, 4] as $id) {
    try { $controller->deleteArchived($id); throw new RuntimeException('Active or foreign record allowed.'); }
    catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {}
}
$actor->allowed = false;
try { $controller->deleteArchived(5); throw new RuntimeException('Missing permission allowed.'); }
catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { if ($e->getStatusCode() !== 403) throw $e; }
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/designations/archive.blade.php'))), TOKEN_PARSE);
echo "Archived designation delete protections and template checks passed.\n";
