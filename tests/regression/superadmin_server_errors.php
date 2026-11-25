<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\{DB, Blade};
use Illuminate\Http\Request;
use App\Models\Central\Plan;

foreach (['superadmin.companies.suspended', 'superadmin.companies.suspend'] as $view) {
    token_get_all(Blade::compileString(file_get_contents(view()->getFinder()->find($view))), TOKEN_PARSE);
}
config(['database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('central');
(require database_path('migrations/central/2026_08_08_000003_create_central_plans_table.php'))->up();
(require database_path('migrations/central/2026_08_08_000001_create_central_companies_table.php'))->up();
(require database_path('migrations/central/2026_08_08_000004_create_central_company_subscriptions_table.php'))->up();
Illuminate\Support\Facades\Auth::guard('web')->setUser(new App\Models\User(['role' => 'superadmin']));
$company = App\Models\Central\Company::create([
    'name' => 'Suspended legacy company', 'email' => 'suspended@example.test',
    'db_name' => 'legacy', 'status' => 'suspended',
]);
$suspensionController = new App\Http\Controllers\SuperAdmin\CompanySuspensionController;
$companies = $suspensionController->index()->getData()['companies'];
if ($companies->count() !== 1 || $companies->first()->id !== $company->id) {
    throw new RuntimeException('Legacy suspended company was not listed.');
}
App\Services\CompanySuspensionSchema::ensure();
if ($company->fresh()->status !== 'suspended') throw new RuntimeException('Schema repair changed company status.');
$request = Request::create('/super-admin/plans', 'POST', [
    'name' => ' gold ', 'monthly_price' => 1234, 'max_users' => 12,
    'max_storage_gb' => 10, 'is_active' => 1,
]);
$controller = new App\Http\Controllers\SuperAdmin\CompanyController;
$controller->storePlan($request);
try {
    $controller->storePlan($request);
    throw new RuntimeException('Duplicate plan was accepted.');
} catch (Illuminate\Validation\ValidationException $e) {
    if (!isset($e->errors()['name'])) throw $e;
}
if (Plan::count() !== 1 || Plan::first()->monthly_price != 1234) {
    throw new RuntimeException('Plan creation changed existing catalog data.');
}
echo "PASS: suspension Blade syntax, legacy suspended-company listing, repeat schema repair, plan creation and duplicate validation.\n";
