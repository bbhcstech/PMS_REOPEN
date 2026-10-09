<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth, DB, Hash, Schema, RateLimiter};
use App\Models\User;
use App\Services\{CompanyDestruction, CompanyContext};
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Validation\ValidationException;

$connector = new class implements Illuminate\Database\Connectors\ConnectorInterface {
    private array $databases = [];
    public array $dropped = [];
    public function connect(array $config) {
        if (in_array($config['database'], $this->dropped, true) || $config['database'] === 'offline') throw new RuntimeException('Database unavailable');
        return $this->databases[$config['database']] ??= new PDO('sqlite::memory:');
    }
};
app()->instance('db.connector.sqlite', $connector);
foreach (['central' => 'registry', 'tenant' => 'primary', 'mysql' => 'primary', 'session_db' => 'primary'] as $name => $database) {
    config(["database.connections.$name" => ['driver' => 'sqlite', 'database' => $database, 'prefix' => '']]); DB::purge($name);
}
config(['database.default' => 'tenant', 'session.driver' => 'array', 'cache.default' => 'array']);
Schema::connection('central')->create('companies', function ($t) {
    $t->id(); foreach (['name', 'db_name', 'email', 'password', 'company_code', 'domain', 'subdomain'] as $c) $t->string($c)->nullable();
    $t->softDeletes(); $t->timestamps();
});
Schema::connection('central')->create('company_subscriptions', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('status'); $t->timestamp('ends_at');
});
foreach (['alpha' => 1, 'beta' => 2, 'offline' => 3] as $name => $id) {
    DB::connection('central')->table('companies')->insert(['id' => $id, 'name' => ucfirst($name), 'db_name' => $name, 'email' => "$name@test.local", 'password' => 'Admin-' . $name, 'company_code' => strtoupper($name), 'domain' => "$name.test", 'subdomain' => "$name.test"]);
}
foreach (['primary', 'alpha', 'beta'] as $database) {
    config(['database.connections.tenant.database' => $database]); DB::purge('tenant');
    Schema::connection('tenant')->create('users', function ($t) {
        $t->id(); $t->integer('company_id')->nullable(); foreach (['name', 'email', 'password', 'raw_password', 'role', 'personal_email', 'remember_token'] as $c) $t->string($c)->nullable();
        $t->boolean('is_active')->default(true); $t->boolean('login_allowed')->default(true); $t->timestamp('archived_at')->nullable(); $t->integer('company_staff_role_id')->nullable(); $t->timestamps();
    });
    Schema::connection('tenant')->create('employee_details', function ($t) { $t->id(); $t->integer('user_id'); $t->string('status')->nullable(); $t->date('exit_date')->nullable(); });
    if ($database === 'primary') continue;
    $id = $database === 'alpha' ? 1 : 2;
    DB::connection('tenant')->table('users')->insert([
        ['id' => 1, 'company_id' => $id, 'name' => "$database admin", 'email' => "$database@test.local", 'password' => Hash::make('Admin-' . $database), 'raw_password' => 'Obsolete-password', 'role' => 'admin', 'is_active' => true],
        ['id' => 2, 'company_id' => $id, 'name' => "$database HR", 'email' => 'same@test.local', 'password' => Hash::make('HR-' . $database), 'raw_password' => 'Obsolete-password', 'role' => 'hr', 'is_active' => true],
        ['id' => 3, 'company_id' => $id, 'name' => 'Blocked', 'email' => "blocked-$database@test.local", 'password' => Hash::make('Blocked-password'), 'raw_password' => 'Obsolete-password', 'role' => 'manager', 'is_active' => false],
    ]);
}
function deletionCheck($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function loginAttempt($email, $password, $companyId = null) {
    Auth::forgetGuards(); session()->flush(); app(CompanyContext::class)->reset();
    $data = ['email' => $email, 'password' => $password]; if ($companyId) $data['company_id'] = $companyId;
    $r = LoginRequest::create('/login', 'POST', $data); $r->setContainer(app()); $r->setLaravelSession(app('session')->driver()); app()->instance('request', $r);
    RateLimiter::clear($r->throttleKey());
    try { $r->authenticate(); return true; } catch (ValidationException $e) {
        deletionCheck(!Auth::guard('web')->check() && !session('current_company_id'), 'Failed login retained authentication/context.');
        return false;
    }
}
deletionCheck(loginAttempt('alpha@test.local', 'Admin-alpha') && Auth::user()->company_id === 1, 'Valid Alpha login failed.');
deletionCheck(loginAttempt('same@test.local', 'HR-beta') && Auth::user()->company_id === 2, 'Same email selected the wrong company.');
deletionCheck(!loginAttempt('same@test.local', 'HR-beta', 1), 'Beta password entered Alpha.');
deletionCheck(!loginAttempt('alpha@test.local', 'Admin-beta'), 'Beta admin password entered Alpha.');
deletionCheck(!loginAttempt('alpha@test.local', 'Obsolete-password'), 'Raw password bypass accepted.');
deletionCheck(!loginAttempt('alpha@test.local', 'admin-alpha'), 'Case-insensitive password accepted.');
deletionCheck(!loginAttempt('alpha@test.local', ' Admin-alpha '), 'Trimmed password accepted.');
deletionCheck(!loginAttempt('blocked-alpha@test.local', 'Blocked-password'), 'Disabled account reactivated.');
deletionCheck(!loginAttempt('hr@company.com', 'Hr@123456'), 'Predefined account was recreated.');
deletionCheck(!loginAttempt('offline@test.local', 'Admin-offline'), 'Unavailable tenant fell back.');
deletionCheck(loginAttempt('alpha@test.local', 'Admin-alpha'), 'Alpha login failed.');
try { (new CompanyDestruction())->destroy(App\Models\Company::findOrFail(2)); throw new RuntimeException('Tenant user destroyed another company.'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { deletionCheck($e->getStatusCode() === 403, 'Wrong deletion authorization.'); }

deletionCheck(loginAttempt('beta@test.local', 'Admin-beta'), 'Beta login failed before deletion.');
$betaSession = session()->all();
$platform = new User(['role' => 'superadmin', 'name' => 'Platform']); $platform->id = 99;
Auth::guard('web')->setUser($platform);
DB::connection('central')->table('companies')->insert(['id' => 4, 'name' => 'Shared primary', 'db_name' => 'primary', 'company_code' => 'PRIMARY']);
try { (new CompanyDestruction())->destroy(App\Models\Company::findOrFail(4)); throw new RuntimeException('Shared primary database was destroyed.'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { deletionCheck($e->getStatusCode() === 409 && !App\Models\Company::findOrFail(4)->trashed(), 'Shared database safety check failed.'); }
$destroy = new class($connector) extends CompanyDestruction {
    public function __construct(private $connector) {}
    protected function dropDatabase(string $database): void { $this->connector->dropped[] = $database; }
};
$destroy->destroy(App\Models\Company::findOrFail(2));
deletionCheck(App\Models\Company::withTrashed()->find(2)->trashed() && $connector->dropped === ['beta'], 'Deletion did not revoke registry and destroy the intended database.');
deletionCheck(DB::connection('central')->table('revoked_company_credentials')->count() >= 3, 'Deleted credentials were not revoked.');
Auth::forgetGuards(); session()->flush(); session()->put($betaSession);
$r = Illuminate\Http\Request::create('/company/session-status', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']); $r->setLaravelSession(app('session')->driver()); app()->instance('request', $r);
$called = false;
$result = (new App\Http\Middleware\SetTenantConnection())->handle($r, function () use (&$called) { $called = true; return response('unsafe'); });
deletionCheck($result->getStatusCode() === 401 && $result->headers->get('X-Company-Deleted') === '1' && !$called && !session('current_company_id'), 'Connected deleted-company role was not signed out.');
deletionCheck(!loginAttempt('beta@test.local', 'Admin-beta') && !loginAttempt('same@test.local', 'HR-beta'), 'Deleted credentials still logged in.');
// An accidental surviving copy must not turn deleted-company credentials into another company's login.
config(['database.connections.tenant.database' => 'alpha']); DB::purge('tenant');
DB::connection('tenant')->table('users')->insert(['id' => 4, 'company_id' => 1, 'name' => 'Copied credentials', 'email' => 'same@test.local', 'password' => Hash::make('HR-beta'), 'role' => 'hr']);
deletionCheck(!loginAttempt('same@test.local', 'HR-beta', 1), 'Revoked credentials reached a present company via a surviving copy.');
deletionCheck(loginAttempt('alpha@test.local', 'Admin-alpha') && loginAttempt('same@test.local', 'HR-alpha'), 'Deleting Beta blocked valid Alpha credentials.');
echo "PASS: exact/company-bound passwords, disabled/predefined/raw-password denial, database destruction, active-session logout, deleted-credential revocation and surviving-company logins.\n";
