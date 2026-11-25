<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\SystemNotificationService;
use App\Http\Middleware\SetTenantConnection;

app()->instance('db.connector.sqlite', new class implements Illuminate\Database\Connectors\ConnectorInterface {
    private array $databases = [];
    public function connect(array $config) {
        if ($config['database'] === 'offline') throw new RuntimeException('Simulated tenant outage');
        return $this->databases[$config['database']] ??= new PDO('sqlite::memory:');
    }
});
foreach (['central' => 'registry', 'tenant' => 'alpha', 'mysql' => 'alpha', 'session_db' => 'primary'] as $name => $database) {
    config(["database.connections.$name" => ['driver' => 'sqlite', 'database' => $database, 'prefix' => '']]);
    DB::purge($name);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Schema::connection('central')->create('companies', function ($t) { $t->id(); $t->string('name'); $t->string('db_name')->unique(); $t->softDeletes(); });
DB::connection('central')->table('companies')->insert([
    ['id' => 1, 'name' => 'Alpha', 'db_name' => 'alpha'],
    ['id' => 2, 'name' => 'Beta', 'db_name' => 'beta'],
    ['id' => 3, 'name' => 'Offline', 'db_name' => 'offline'],
]);
foreach (['alpha', 'beta', 'primary'] as $database) {
    config(['database.connections.tenant.database' => $database]); DB::purge('tenant');
    Schema::connection('tenant')->create('users', function ($t) {
        $t->id(); $t->string('name'); $t->string('email'); $t->integer('company_id')->nullable();
        $t->string('role'); $t->boolean('is_active')->default(true); $t->string('remember_token')->nullable(); $t->timestamps();
    });
    Schema::connection('tenant')->create('notifications', function ($t) {
        $t->uuid('id')->primary(); $t->string('type'); $t->string('notifiable_type'); $t->unsignedBigInteger('notifiable_id');
        $t->text('data'); $t->timestamp('read_at')->nullable(); $t->timestamps(); $t->index(['notifiable_type', 'notifiable_id']);
    });
    DB::connection('tenant')->table('users')->insert(['id' => 1, 'name' => ucfirst($database) . ' Admin', 'email' => 'same@example.test', 'company_id' => $database === 'beta' ? 2 : 1, 'role' => 'admin']);
}
function isolatedCheck($ok, $message) { if (!$ok) throw new RuntimeException($message); }
$session = app('session')->driver();
function isolatedRequest($companyId, $database) {
    global $session;
    Auth::forgetGuards();
    $session->flush();
    $session->put(['current_company_id' => $companyId, 'current_company_db' => $database, Auth::guard('web')->getName() => 1]);
    $request = Request::create('/notifications/latest', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $request->setLaravelSession($session); app()->instance('request', $request);
    return $request;
}
$middleware = new SetTenantConnection();
foreach ([[1, 'alpha'], [2, 'beta'], [1, 'alpha']] as [$companyId, $database]) {
    $response = $middleware->handle(isolatedRequest($companyId, $database), function () use ($companyId, $database) {
        isolatedCheck(Auth::user()->name === ucfirst($database) . ' Admin' && (int) Auth::user()->company_id === $companyId, 'Colliding user ID resolved in another company.');
        isolatedCheck(app(App\Services\CompanyContext::class)->id() === $companyId, 'Cached company context crossed requests.');
        SystemNotificationService::notifyAllRoles('Private ' . $database, 'Company-only message');
        return response('ok');
    });
    isolatedCheck($response->getStatusCode() === 200, 'Valid workspace blocked.');
}
isolatedCheck(User::find(1)->notifications()->count() === 2, 'Alpha notifications mixed with Beta.');
$actor = Auth::user(); $actor->role = 'superadmin';
isolatedCheck(!App\Services\TenantScope::isPlatformAdmin(), 'A tenant role name elevated a company user to platform access.');
$actor->role = 'admin';
DB::connection('tenant')->table('users')->insert([
    ['id' => 2, 'name' => 'Foreign', 'email' => 'foreign@test', 'company_id' => 2, 'role' => 'hr'],
    ['id' => 3, 'name' => 'Unassigned', 'email' => 'none@test', 'company_id' => null, 'role' => 'hr'],
]);
isolatedCheck(SystemNotificationService::roleUsers()->pluck('id')->all() === [1], 'Foreign or unassigned recipients included.');
SystemNotificationService::notifyUser([1, 2, 3], 'Explicit recipients', 'Must stay in Alpha');
isolatedCheck(DB::connection('tenant')->table('notifications')->where('notifiable_id', '!=', 1)->count() === 0, 'Direct recipients crossed company boundary.');
try { User::find(2)->notify(new App\Notifications\SystemNotification(['title' => 'Forbidden'])); throw new RuntimeException('Direct notification bypassed isolation.'); }
catch (LogicException $e) {}
// Historical tagged foreign notifications must not appear or be mutated through the owner relation.
DB::connection('tenant')->table('notifications')->insert(['id' => 'foreign', 'type' => 'test', 'notifiable_type' => User::class, 'notifiable_id' => 1, 'data' => json_encode(['company_id' => 2]), 'created_at' => now(), 'updated_at' => now()]);
isolatedCheck(!Auth::user()->notifications()->whereKey('foreign')->exists(), 'Foreign tagged notification visible.');
foreach ([[1, 'beta', 403], [null, 'beta', 403], [null, null, 403], [3, 'offline', 503]] as [$id, $db, $status]) {
    $called = false;
    try {
        $middleware->handle(isolatedRequest($id, $db), function () use (&$called) { $called = true; return response('unsafe'); });
        throw new RuntimeException('Invalid or offline workspace proceeded.');
    } catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
        isolatedCheck($e->getStatusCode() === $status && !$called, 'Workspace did not fail closed.');
    }
}
$middleware->handle(isolatedRequest(2, 'beta'), function () {
    isolatedCheck(Auth::user()->notifications()->count() === 1, 'Beta inherited Alpha notifications.');
    return response('ok');
});
Schema::connection('central')->create('central_notifications', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('target_audience'); $t->boolean('is_read')->default(false); $t->timestamp('read_at')->nullable(); $t->timestamps();
});
DB::connection('central')->table('central_notifications')->insert([
    ['id' => 1, 'company_id' => 1, 'target_audience' => 'company_admin'],
    ['id' => 2, 'company_id' => 2, 'target_audience' => 'company_admin'],
    ['id' => 3, 'company_id' => 2, 'target_audience' => 'super_admin'],
]);
$center = new App\Http\Controllers\Admin\CompanyNotificationController();
isolatedCheck($center->unreadCount()->getData(true)['count'] === 1, 'Company notification count included foreign/platform alerts.');
foreach ([1, 3] as $id) {
    try { $center->markAsRead($id); throw new RuntimeException('Foreign/platform notification was marked read.'); }
    catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {}
}
$center->markAllRead();
isolatedCheck((bool) DB::connection('central')->table('central_notifications')->where('id', 2)->value('is_read')
    && !DB::connection('central')->table('central_notifications')->where('id', 1)->value('is_read')
    && !DB::connection('central')->table('central_notifications')->where('id', 3)->value('is_read'), 'Bulk read changed another company/platform alerts.');
DB::connection('central')->table('companies')->where('id', 2)->update(['deleted_at' => now()]);
$called = false;
$deleted = $middleware->handle(isolatedRequest(2, 'beta'), function () use (&$called) { $called = true; return response('unsafe'); });
isolatedCheck($deleted->getStatusCode() === 401 && !$called && !$session->has('current_company_id')
    && !$session->has(Auth::guard('web')->getName()), 'Deleted company retained access.');
echo "PASS: colliding user IDs, company context, notification broadcasts/direct writes/reads, database mismatches, outages and sequential company requests are isolated.\n";
