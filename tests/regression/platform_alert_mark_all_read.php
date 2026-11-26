<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth, DB, Blade};
use App\Models\Central\{Company, CentralNotification};
use App\Http\Controllers\SuperAdmin\CompanyController;
use Illuminate\Http\Request;
config(['database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('central');
foreach (['2026_08_08_000001_create_central_companies_table.php', '2026_08_08_000003_create_central_plans_table.php',
    '2026_08_08_000004_create_central_company_subscriptions_table.php', '2026_08_18_000003_create_central_complaints_and_notifications_tables.php'] as $file) {
    (require database_path('migrations/central/' . $file))->up();
}
function alertCheck($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$company = Company::create(['name' => 'Alpha', 'email' => 'alpha@example.test', 'db_name' => 'alpha', 'status' => 'trial', 'trial_ends_at' => now()->addDays(3)]);
$tenant = CentralNotification::createNotification(['company_id' => $company->id, 'type' => 'TEST', 'title' => 'Tenant only', 'message' => 'Tenant only', 'target_audience' => 'company_admin']);
foreach (range(1, 55) as $i) CentralNotification::createNotification([
    'company_id' => $company->id, 'type' => 'TEST', 'title' => "Platform $i", 'message' => "Alert $i", 'target_audience' => 'super_admin', 'is_read' => false,
]);
$controller = new CompanyController;
$request = Request::create('/super-admin/alerts/mark-all-read', 'POST');
Auth::guard('web')->setUser(new App\Models\User(['role' => 'admin']));
try {
    $controller->markAllAlertsRead($request);
    throw new RuntimeException('Non-platform admin was allowed.');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { alertCheck($e->getStatusCode() === 403, 'Wrong denial status.'); }
Auth::guard('web')->setUser(new App\Models\User(['role' => 'superadmin']));
$before = $controller->alerts($request)->getData();
alertCheck($before['kpis']['unread'] > 0, 'Missing unread fixtures.');
alertCheck($controller->markAllAlertsRead($request)->getData()->success, 'Mark all failed.');
alertCheck(CentralNotification::where('target_audience', 'super_admin')->where('is_read', false)->count() === 0, 'Platform notifications outside first 50 were not marked.');
alertCheck(!$tenant->fresh()->is_read, 'Tenant-only notification was changed.');
$after = $controller->alerts($request)->getData();
alertCheck($after['kpis']['unread'] === 0, 'Read status did not survive reload.');
alertCheck($after['kpis']['critical'] === $before['kpis']['critical'] && $after['kpis']['resolved_today'] === $before['kpis']['resolved_today'], 'Acknowledgement changed severity or resolution.');
$controller->markAllAlertsRead($request);
CentralNotification::createNotification(['type' => 'TEST', 'title' => 'New platform alert', 'message' => 'New event', 'target_audience' => 'super_admin'])->forceFill(['created_at' => now()->addSecond()])->save();
$newData = $controller->alerts($request)->getData();
alertCheck($newData['kpis']['unread'] === 1, 'New alerts were incorrectly marked read.');
token_get_all(Blade::compileString(file_get_contents(resource_path('views/superadmin/alerts/index.blade.php'))), TOKEN_PARSE);
echo "PASS: platform authorization, all database/generated alerts, persistence, repeat action, tenant isolation, new events and Blade syntax.\n";
