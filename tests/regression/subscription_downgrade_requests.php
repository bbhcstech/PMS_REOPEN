<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth, DB, Schema};
use App\Models\Central\{Company, CompanyComplaint, Plan, CentralNotification};
use App\Models\User;
use App\Services\{SubscriptionService, SubscriptionChangeSchema, SubscriptionDowngradeRequests};
foreach (['central', 'tenant'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]); DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
foreach (['2026_08_08_000001_create_central_companies_table.php', '2026_08_08_000003_create_central_plans_table.php',
    '2026_08_08_000004_create_central_company_subscriptions_table.php', '2026_10_01_000001_add_manually_suspended_to_companies_table.php',
    '2026_08_18_000003_create_central_complaints_and_notifications_tables.php', '2026_08_08_000007_create_central_activity_logs_table.php',
    '2026_10_09_180000_add_subscription_downgrade_requests.php'] as $file) {
    $path = database_path('migrations/central/' . $file); if (is_file($path)) (require $path)->up();
}
if (!Schema::connection('central')->hasTable('super_admin_activity_logs')) Schema::connection('central')->create('super_admin_activity_logs', function ($t) {
    $t->id(); $t->integer('super_admin_id')->nullable(); $t->integer('company_id')->nullable(); $t->string('action'); $t->text('description')->nullable();
    $t->text('meta')->nullable(); $t->string('ip_address')->nullable(); $t->text('user_agent')->nullable(); $t->timestamps();
});
SubscriptionChangeSchema::ensure();
foreach (App\Support\SupportedPlans::defaults() as $slug => $data) Plan::create($data + ['slug' => $slug, 'is_active' => true]);
$a = Company::create(['name' => 'Alpha', 'email' => 'alpha@test.com', 'db_name' => 'alpha', 'status' => 'active']);
$b = Company::create(['name' => 'Beta', 'email' => 'beta@test.com', 'db_name' => 'beta', 'status' => 'active']);
$platform = new User(['name' => 'Platform', 'role' => 'superadmin']); $platform->id = 99;
$admin = new User(['name' => 'Alpha admin', 'email' => 'admin@alpha.test', 'role' => 'admin', 'company_id' => $a->id]); $admin->id = 7;
$gold = Plan::where('slug', 'gold')->first(); $diamond = Plan::where('slug', 'diamond')->first(); $free = Plan::where('slug', 'free')->first();
Auth::guard('web')->setUser($platform);
$subscriptions = app(SubscriptionService::class); $requests = app(SubscriptionDowngradeRequests::class);
$subscriptions->activateOrUpgradePlan($a, $diamond); $subscriptions->activateOrUpgradePlan($b, $gold);
function downgradeCheck($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function blockedDowngrade($fn) { try { $fn(); throw new RuntimeException('Downgrade unexpectedly allowed'); } catch (InvalidArgumentException $e) {} }
function requestData($plan) { return ['requested_plan_id' => $plan->id, 'subject' => 'Reduce subscription', 'description' => 'Our company no longer needs the higher tier.', 'priority' => 'MEDIUM']; }
Auth::guard('web')->setUser($admin);
blockedDowngrade(fn () => $subscriptions->activateOrUpgradePlan($a->fresh(), $free));
$ticket = $requests->create(requestData($gold), $a->fresh(), $admin);
downgradeCheck($ticket->plan_request_status === 'pending' && $a->fresh()->activeSubscription->plan_id === $diamond->id, 'Submitting request changed the plan.');
downgradeCheck(CentralNotification::where('company_id', $a->id)->where('target_audience', 'super_admin')->exists(), 'Super Admin was not notified.');
try { $requests->create(requestData($free), $a->fresh(), $admin); throw new RuntimeException('Duplicate pending request allowed.'); } catch (Illuminate\Validation\ValidationException $e) {}
try { $requests->create(requestData($free), $b->fresh(), $admin); throw new RuntimeException('Foreign company request allowed.'); } catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { downgradeCheck($e->getStatusCode() === 403, 'Wrong foreign request denial.'); }
try { $requests->decide($ticket->id, 'approved'); throw new RuntimeException('Company approved its own downgrade.'); } catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { downgradeCheck($e->getStatusCode() === 403, 'Wrong approval denial.'); }
Auth::guard('web')->setUser($platform);
$requests->decide($ticket->id, 'approved');
downgradeCheck($ticket->fresh()->plan_request_status === 'approved' && $a->fresh()->activeSubscription->plan_id === $gold->id && $a->fresh()->max_users === 25, 'Approved lower plan/limits did not apply.');
downgradeCheck($a->fresh()->highest_plan_level === 3 && $b->fresh()->activeSubscription->plan_id === $gold->id, 'Historical tier or another company changed.');
blockedDowngrade(fn () => $requests->decide($ticket->id, 'approved'));
$subscriptions->activateOrUpgradePlan($a->fresh(), $gold); // Renewal of the approved tier remains allowed.
blockedDowngrade(fn () => $subscriptions->activateOrUpgradePlan($a->fresh(), $free));
Auth::guard('web')->setUser($admin);
$declined = $requests->create(requestData($free), $a->fresh(), $admin);
Auth::guard('web')->setUser($platform); $requests->decide($declined->id, 'rejected');
downgradeCheck($declined->fresh()->plan_request_status === 'rejected' && $a->fresh()->activeSubscription->plan_id === $gold->id, 'Decline changed the plan.');
Auth::guard('web')->setUser($admin); $returnToFree = $requests->create(requestData($free), $a->fresh(), $admin);
Auth::guard('web')->setUser($platform); $requests->decide($returnToFree->id, 'approved');
downgradeCheck($a->fresh()->activeSubscription->plan_id === $free->id && $a->fresh()->max_users === 5, 'Approved return to Free failed.');
downgradeCheck(CentralNotification::where('company_id', $a->id)->where('target_audience', 'company_admin')->exists(), 'Company did not receive the decision notification.');
$subscriptions->activateOrUpgradePlan($a->fresh(), $gold);
blockedDowngrade(fn () => $subscriptions->activateOrUpgradePlan($a->fresh(), $free));
Auth::guard('web')->setUser($admin); $stale = $requests->create(requestData($free), $a->fresh(), $admin);
Auth::guard('web')->setUser($platform); $subscriptions->activateOrUpgradePlan($a->fresh(), $diamond);
$requests->decide($stale->id, 'approved');
downgradeCheck($stale->fresh()->plan_request_status === 'stale' && $a->fresh()->activeSubscription->plan_id === $diamond->id, 'Stale request changed a newer subscription.');
Auth::guard('web')->setUser($admin); $failed = $requests->create(requestData($gold), $a->fresh(), $admin);
Auth::guard('web')->setUser($platform);
DB::connection('central')->statement("CREATE TRIGGER fail_downgrade BEFORE INSERT ON company_subscriptions BEGIN SELECT RAISE(ABORT, 'atomic test failure'); END");
try { $requests->decide($failed->id, 'approved'); throw new RuntimeException('Failure trigger did not abort.'); } catch (Illuminate\Database\QueryException $e) {}
downgradeCheck($failed->fresh()->plan_request_status === 'pending' && $a->fresh()->activeSubscription->plan_id === $diamond->id, 'Failed approval left a partial change.');
DB::connection('central')->statement('DROP TRIGGER fail_downgrade');
echo "PASS: Support Desk request/notifications, approval-only paid/Free downgrades, rejection, renewal, upgrade lock, replay/duplicate/company authorization and historical tier preservation.\n";
