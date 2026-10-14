<?php

require __DIR__.'/company_module_access.php';

use App\Models\WorkspaceModule;
use Illuminate\Support\Facades\DB;

DB::connection('central')->table('modules')->insert(['id' => 8, 'name' => 'Notifications', 'slug' => 'notifications', 'is_active' => true]);
WorkspaceModule::create(['name' => 'Unprovided custom module', 'slug' => 'unprovided-custom', 'is_active' => true]);
$catalogCount = DB::connection('central')->table('modules')->count();
$companyA = new class {
    public function hasFeature($slug) { return in_array($slug, ['tasks', 'notifications'], true); }
};
$companyB = new class {
    public function hasFeature($slug) { return $slug === 'orders'; }
};
$service = app(App\Services\AdminWorkspaceModules::class);
$modulesA = $service->companyModules($companyA);
$assert($modulesA->pluck('slug')->sort()->values()->all() === ['notifications', 'tasks'], 'Company list differs from provided platform modules');
$assert($modulesA->firstWhere('slug', 'tasks')->name === 'Tasks', 'Module Details did not use platform module identity');
$assert(WorkspaceModule::where('slug', 'notifications')->exists(), 'Newly provided platform module was not synchronized');
WorkspaceModule::where('slug', 'tasks')->update(['is_active' => false]);
$assert(!$service->companyModules($companyA)->firstWhere('slug', 'tasks')->is_active, 'Company-local disabled state was overwritten');
$assert($service->companyModules($companyB)->pluck('slug')->all() === ['orders'], 'One company inherited another company module selection');
DB::connection('central')->table('modules')->where('slug', 'notifications')->update(['is_active' => false]);
$assert($service->companyModules($companyA)->pluck('slug')->all() === ['tasks'], 'Inactive platform module remained in company list');
$assert(DB::connection('central')->table('modules')->count() === $catalogCount, 'Viewing company modules mutated platform catalog');
echo "PASS: provided company module catalog, canonical details, new modules, local status, distinct company entitlements, platform revocation, read-only platform catalog.\n";
