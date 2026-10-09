<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Module;
use App\Services\AdminWorkspaceModules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('central');
Schema::connection('central')->create('modules', function ($t) {
    $t->id(); $t->string('name'); $t->string('slug')->unique();
    foreach (['icon', 'description', 'route_prefix', 'route_name'] as $field) $t->string($field)->nullable();
    $t->unsignedBigInteger('parent_id')->nullable();
    $t->boolean('is_core')->default(false); $t->boolean('is_active')->default(true);
    $t->integer('sort_order')->default(0); $t->timestamps();
});
function moduleCheck($condition, $message) { if (! $condition) throw new RuntimeException($message); }
$inactive = Module::create(['name' => 'Tasks', 'slug' => 'tasks', 'is_active' => false]);
foreach (['bye', 'kjm', 'unused-placeholder'] as $slug) Module::create(['slug' => $slug, 'name' => $slug]);
$custom = Module::create(['slug' => 'custom-employees', 'name' => 'Employee Records', 'route_name' => 'employees.index']);
Module::create(['slug' => 'invalid-route', 'name' => 'Invalid', 'route_name' => 'missing.workspace.index']);
$catalog = app(AdminWorkspaceModules::class)->all();
moduleCheck($catalog->contains('id', $inactive->id), 'Inactive workspace features must remain visible.');
moduleCheck(! $catalog->firstWhere('id', $inactive->id)->is_active, 'Viewing the catalog reactivated a disabled module.');
moduleCheck($catalog->contains('id', $custom->id), 'A custom module connected to a workspace route was omitted.');
foreach (['bye', 'kjm', 'unused-placeholder', 'invalid-route'] as $slug) {
    moduleCheck(! $catalog->contains('slug', $slug), 'Unimplemented feature appeared in catalog: ' . $slug);
    moduleCheck(Module::where('slug', $slug)->exists(), 'Historical module data must remain intact.');
}
foreach (['projects', 'orders', 'products', 'community', 'employees', 'designations', 'payroll', 'attendance-settings'] as $slug) {
    moduleCheck($catalog->contains('slug', $slug), 'Current workspace module missing: ' . $slug);
}
moduleCheck($catalog->firstWhere('slug', 'designations')->category === 'HR & PEOPLE', 'Workspace categories were lost.');
$count = Module::count();
app(AdminWorkspaceModules::class)->all();
moduleCheck(Module::count() === $count, 'Repeat catalog visits created duplicate modules.');
moduleCheck(! $inactive->fresh()->is_active, 'Repeat visits changed module status.');
$compiled = app('blade.compiler')->compileString(file_get_contents(resource_path('views/superadmin/subscriptions/index.blade.php')));
token_get_all($compiled, TOKEN_PARSE);
echo "PASS: real workspace catalog, active/inactive retention, invalid entry exclusion, custom routes, categories, repeat visits and template compilation.\n";
