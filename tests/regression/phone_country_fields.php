<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Http\Request;
use App\Support\CountryPhone;
use App\Http\Middleware\NormalizePhoneCountryCodes;
app()->instance('db.connector.sqlite', new class implements Illuminate\Database\Connectors\ConnectorInterface {
    private array $databases = [];
    public function connect(array $config) { return $this->databases[$config['database']] ??= new PDO('sqlite::memory:'); }
});
function phoneDb($name) { config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => $name, 'prefix' => ''], 'database.default' => 'tenant']); DB::purge('tenant'); }
function phoneCheck($ok, $message) { if (!$ok) throw new RuntimeException($message); }
foreach (['alpha', 'beta'] as $db) {
    phoneDb($db);
    (require database_path('migrations/tenant/2026_10_09_150000_ensure_tenant_country_phone_codes.php'))->up();
    DB::table('countries')->insert(['name' => 'Database custom ' . $db, 'phone_code' => $db === 'alpha' ? '+9988' : '+9987', 'iso_code' => 'ZZ', 'min_digits' => 5, 'max_digits' => 9]);
}
phoneDb('alpha');
config(['session.driver' => 'array']);
$session = app('session')->driver();
$base = Request::create('/'); $base->setLaravelSession($session); app()->instance('request', $base);
$map = CountryPhone::formMap();
phoneCheck(isset($map['Database custom alpha']) && !isset($map['Database custom beta']), 'Database countries leaked between companies.');
phoneCheck(CountryPhone::meta('Database custom alpha')['dial_code'] === '+9988', 'Database country metadata was ignored.');
$html = view('partials.phone-country-select', ['phoneField' => 'whatsapp', 'phoneValue' => '+9988 12345'])->render();
phoneCheck(str_contains($html, 'value="+9988"') && str_contains($html, 'selected'), 'Saved country code did not render selected.');
phoneDb('beta');
phoneCheck(isset(CountryPhone::formMap()['Database custom beta']) && !isset(CountryPhone::formMap()['Database custom alpha']), 'Sequential country-cache isolation failed.');
phoneDb('alpha');
function normalizePhone(array $data) {
    $request = Request::create('/save', 'POST', $data); $request->setLaravelSession(app('session')->driver()); app()->instance('request', $request);
    (new NormalizePhoneCountryCodes())->handle($request, fn () => response('ok')); return $request;
}
$request = normalizePhone(['_pms_phone_codes' => ['whatsapp' => '+9988', 'alternate_phone' => '+44'], 'whatsapp' => '12345', 'alternate_phone' => '20 1234 5678', 'name' => 'Unchanged', 'email' => 'unchanged@test.com']);
phoneCheck($request->whatsapp === '+9988 12345' && $request->alternate_phone === '+44 2012345678', 'Multiple independent country codes did not save.');
phoneCheck($request->name === 'Unchanged' && $request->email === 'unchanged@test.com' && !$request->has('_pms_phone_codes'), 'Unrelated fields or request metadata changed.');
$request = normalizePhone(['_pms_phone_codes' => ['phone' => '+44'], 'phone' => '+44 2012345678']);
phoneCheck($request->phone === '+44 2012345678', 'Saved international prefix was duplicated.');
$request = normalizePhone(['_pms_phone_codes' => ['phone' => '+91'], 'phone' => '9198765432']);
phoneCheck($request->phone === '+91 9198765432', 'National number beginning with country digits was truncated.');
$request = normalizePhone(['_pms_phone_codes' => ['phone' => '+91'], 'phone' => '', 'mobile' => '+44 2012345678']);
phoneCheck($request->phone === '' && $request->mobile === '+44 2012345678', 'Empty optional field or existing control changed.');
foreach ([['phone' => 'INVALID'], ['phone' => '+9987'], ['phone' => '+91', 'name' => '+91']] as $codes) {
    try { normalizePhone(['_pms_phone_codes' => $codes, 'phone' => '12345678']); throw new RuntimeException('Untrusted country/field accepted.'); }
    catch (Illuminate\Validation\ValidationException|Symfony\Component\HttpKernel\Exception\HttpException $e) {}
}
echo "PASS: database-backed country options, independent numbers, saved-prefix preservation, optional values, input validation and sequential tenant isolation.\n";
