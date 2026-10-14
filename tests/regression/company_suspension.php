<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth, DB, Schema};
use App\Models\Central\{Company, Plan, Subscription, CentralNotification};
use App\Models\User;
use App\Services\{CompanySuspension, CompanyDestruction, SubscriptionService, SubscriptionChangeSchema};
app()->instance('db.connector.sqlite', new class implements Illuminate\Database\Connectors\ConnectorInterface {
    private array $databases = [];
    public function connect(array $config) { return $this->databases[$config['database']] ??= new PDO('sqlite::memory:'); }
});
foreach (['central' => 'registry', 'tenant' => 'primary', 'session_db' => 'sessions'] as $connection => $database) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => $database, 'prefix' => '']]); DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
foreach (['2026_08_08_000001_create_central_companies_table.php', '2026_08_08_000003_create_central_plans_table.php',
    '2026_08_08_000004_create_central_company_subscriptions_table.php', '2026_10_01_000001_add_manually_suspended_to_companies_table.php',
    '2026_08_18_000003_create_central_complaints_and_notifications_tables.php', '2026_10_09_190000_add_company_suspension_reasons.php'] as $file) {
    (require database_path('migrations/central/' . $file))->up();
}
SubscriptionChangeSchema::ensure();
Schema::connection('central')->table('companies', fn ($t) => $t->string('password')->nullable());
function suspensionCheck($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function suspensionDenied($fn, $code) {
    try { $fn(); throw new RuntimeException('Action unexpectedly allowed.'); }
    catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { suspensionCheck($e->getStatusCode() === $code, 'Wrong rejection code.'); }
}
$plan = Plan::create(App\Support\SupportedPlans::defaults()['gold'] + ['slug' => 'gold', 'is_active' => true]);
$alpha = Company::create(['name' => 'Alpha', 'email' => 'alpha@test.com', 'phone' => '+91 98765 43210', 'db_name' => 'alpha', 'status' => 'active']);
$beta = Company::create(['name' => 'Beta', 'email' => 'beta@test.com', 'db_name' => 'beta', 'status' => 'active']);
$alphaSub = Subscription::create(['company_id' => $alpha->id, 'plan_id' => $plan->id, 'starts_at' => now(), 'ends_at' => now()->addMonth(), 'status' => 'active']);
$betaSub = Subscription::create(['company_id' => $beta->id, 'plan_id' => $plan->id, 'starts_at' => now(), 'ends_at' => now()->addMonth(), 'status' => 'active']);
$platform = new User(['name' => 'Platform', 'role' => 'superadmin']); $platform->id = 99;
$admin = new User(['role' => 'admin', 'company_id' => $alpha->id]); $admin->id = 7;
$service = app(CompanySuspension::class);
$reason = ['reason_category' => 'Data leak', 'reason' => 'An incident is under security review.'];
Auth::guard('web')->setUser($admin);
suspensionDenied(fn () => $service->suspend($beta->id, $reason), 403);
suspensionDenied(fn () => $service->reactivate($beta->id), 403);
suspensionDenied(fn () => (new CompanyDestruction())->destroy(App\Models\Company::find($alpha->id)), 403);
Auth::guard('web')->setUser($platform);
foreach ([[], ['reason_category' => 'Bad category', 'reason' => 'Explain this suspension'], ['reason_category' => 'Other', 'reason' => '                    ']] as $invalid) {
    try { $service->suspend($alpha->id, $invalid); throw new RuntimeException('Invalid suspension accepted.'); }
    catch (Illuminate\Validation\ValidationException $e) {}
}
$destroy = new class extends CompanyDestruction { public $dropped = []; protected function dropDatabase(string $database): void { $this->dropped[] = $database; } };
suspensionDenied(fn () => $destroy->destroy(App\Models\Company::find($alpha->id)), 409);
suspensionCheck(!$alpha->fresh()->trashed() && !$destroy->dropped, 'Ongoing company was deleted.');
$service->suspend($alpha->id, $reason);
suspensionCheck($alpha->fresh()->manually_suspended && $alphaSub->fresh()->status === 'suspended', 'Suspension did not persist.');
suspensionCheck($alpha->fresh()->suspension_reason === $reason['reason'] && $alpha->fresh()->suspended_by == 99, 'Reason/actor missing.');
suspensionCheck($beta->fresh()->status === 'active' && $betaSub->fresh()->status === 'active', 'Another company changed.');
suspensionCheck(CentralNotification::where('company_id', $alpha->id)->where('type', 'COMPANY_SUSPENDED')->where('target_audience', 'company_admin')->count() === 1, 'Missing scoped notification.');
suspensionCheck(str_starts_with($service->whatsappUrl($alpha->fresh()), 'https://wa.me/919876543210?text=') && str_contains(urldecode($service->whatsappUrl($alpha->fresh())), $reason['reason']), 'Incorrect WhatsApp recipient/reason.');
$beta->phone = '9876543210'; suspensionCheck($service->whatsappUrl($beta) === null, 'Guessed country code.');
suspensionDenied(fn () => $service->suspend($alpha->id, $reason), 409);
$end = $alphaSub->ends_at->toDateString();
DB::connection('central')->unprepared("CREATE TRIGGER deny_reactivation_notice BEFORE INSERT ON central_notifications WHEN NEW.type = 'COMPANY_REACTIVATED' BEGIN SELECT RAISE(ABORT, 'notification unavailable'); END");
try { $service->reactivate($alpha->id); throw new RuntimeException('Failed reactivation unexpectedly succeeded.'); }
catch (Illuminate\Database\QueryException $e) {}
suspensionCheck($alpha->fresh()->manually_suspended && $alphaSub->fresh()->status === 'suspended', 'Failed reactivation was only partially rolled back.');
DB::connection('central')->unprepared('DROP TRIGGER deny_reactivation_notice');
$service->reactivate($alpha->id);
suspensionCheck($alpha->fresh()->status === 'active' && !$alpha->fresh()->manually_suspended && $alphaSub->fresh()->status === 'active', 'Reactivation left company suspended.');
suspensionCheck($alphaSub->fresh()->ends_at->toDateString() === $end, 'Reactivation extended subscription.');
$service->suspend($alpha->id, $reason);
$alphaSub->update(['ends_at' => now()->subDay()]);
$service->reactivate($alpha->id);
suspensionCheck($alpha->fresh()->status === 'expired' && $alphaSub->fresh()->status === 'expired', 'Expired subscription was reactivated.');
$service->suspend($alpha->id, $reason);
$destroy->destroy(App\Models\Company::find($alpha->id));
suspensionCheck(Company::withTrashed()->find($alpha->id)->trashed() && $destroy->dropped === ['alpha'] && $beta->fresh()->status === 'active', 'Suspended deletion/isolation failed.');
echo "PASS: required reasons, platform authorization, ongoing deletion block, suspension/notifications, WhatsApp recipient, reactivation without extending dates, expiry and suspended-only deletion.\n";
