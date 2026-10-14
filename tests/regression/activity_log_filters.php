<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB, Auth, Blade};
use Illuminate\Http\Request;
use App\Services\ActivityLogFilters;
function activityCheck($ok, $message) { if (!$ok) throw new RuntimeException($message); }
Illuminate\Support\Carbon::setTestNow('2026-10-09 12:00:00');
$base = ['role' => 'Super Admin', 'company_id' => 1, 'module' => 'Subscriptions', 'action_type' => 'Updated', 'action' => 'Subscription updated', 'status' => 'success', 'is_security' => false, 'timestamp' => now(), 'user_name' => 'Platform', 'user_email' => 'platform@example.test', 'company_name' => 'Alpha', 'resource' => 'Subscription', 'ip_address' => '127.0.0.1'];
$events = collect([$base, array_replace($base, ['role' => 'Company Admin', 'company_id' => 2, 'module' => 'User Management', 'action' => 'Permission changed', 'action_type' => 'Permission Changed', 'status' => 'failed', 'is_security' => true, 'timestamp' => now()->subDay(), 'user_name' => 'Tenant'])]);
foreach ([['actor'=>'Super Admin'], ['company'=>1], ['module'=>'Subscriptions'], ['result'=>'success'], ['search'=>'Alpha', 'actor'=>'Super Admin'], ['date'=>'today'], ['date'=>'yesterday'], ['module'=>'Users'], ['action'=>'Security'], ['action'=>'Permission Changed']] as $filter) {
    activityCheck(ActivityLogFilters::apply($events, Request::create('/', 'GET', $filter))->count() === 1, 'Filter failed: '.json_encode($filter));
}
activityCheck(ActivityLogFilters::apply($events, Request::create('/', 'GET', ['actor'=>'Super Admin','company'=>2]))->isEmpty(), 'Combined filters returned unrelated rows.');
foreach (['7days', '30days'] as $range) activityCheck(ActivityLogFilters::apply($events, Request::create('/', 'GET', ['date'=>$range]))->count() === 2, 'Range failed.');
config(['database.connections.central'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]); DB::purge('central');
foreach (['2026_08_08_000001_create_central_companies_table.php', '2026_08_08_000002_create_central_super_admins_table.php', '2026_08_08_000005_create_central_super_admin_activity_logs_table.php'] as $file) (require database_path('migrations/central/'.$file))->up();
App\Models\Central\Company::create(['name'=>'Alpha','email'=>'alpha@example.test','db_name'=>'alpha']);
$log = App\Models\Central\SuperAdminActivityLog::create(['action'=>'company.updated','description'=>'Recorded company change','ip_address'=>'10.0.0.5','meta'=>['name'=>'Alpha']]);
Auth::guard('web')->setUser(new App\Models\User(['role'=>'superadmin']));
$controller = new App\Http\Controllers\SuperAdmin\CompanyController;
$id = 'EVT-SA-'.str_pad($log->id,5,'0',STR_PAD_LEFT);
$detail = $controller->tenantAuditEvent(Request::create('/'),$id)->getData(true)['event'];
activityCheck($detail['description']==='Recorded company change' && $detail['ip_address']==='10.0.0.5', 'Details did not match selected record.');
try { $controller->tenantAuditEvent(Request::create('/'),'EVT-SA-99999'); throw new RuntimeException('Missing record accepted.'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { activityCheck($e->getStatusCode()===404,'Incorrect missing-record status.'); }
token_get_all(Blade::compileString(file_get_contents(resource_path('views/superadmin/tenant_audit/index.blade.php'))), TOKEN_PARSE);
echo "PASS: every filter, combined/empty results, date windows, selected record details, missing-record rejection and Blade syntax.\n";
