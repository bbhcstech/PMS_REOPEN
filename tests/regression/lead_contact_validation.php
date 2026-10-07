<?php

// Run with: php tests/regression/lead_contact_validation.php
// All database checks use disposable in-memory databases.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\LeadContactController;
use App\Imports\LeadsImport;
use App\Support\LeadContactValidation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Validators\RowValidator;

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => [
        'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
    ]]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array', 'cache.default' => 'array']);

Schema::create('countries', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('phone_code');
    $table->string('iso_code');
    $table->integer('min_digits');
    $table->integer('max_digits');
    $table->string('flag_url')->nullable();
});
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
});
Schema::create('lead_contacts', function (Blueprint $table) {
    $table->id();
    $table->string('contact_name');
});
DB::table('lead_contacts')->insert(['id' => 1, 'contact_name' => 'Test lead']);
DB::table('users')->insert(['id' => 1, 'name' => 'Lead owner']);

function checkLead(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$cases = [
    ['optional numbers', [], true],
    ['blank numbers', ['phone' => '  ', 'mobile' => ''], true],
    ['India national number', ['phone' => '9876543210'], true],
    ['national number starting with dial digits', ['phone' => '9100001234'], true],
    ['formatted full number', ['phone' => '+91 (98765) 43210'], true],
    ['US selected code', ['mobile' => '202-555-0123', 'mobile_country_code' => '+1'], true],
    ['international paste', ['phone' => '+44 7700 900123'], true],
    ['phone letters', ['phone' => '98765abc10'], false],
    ['short phone', ['phone' => '123'], false],
    ['long phone', ['phone' => '12345678901234567890'], false],
    ['short mobile', ['mobile' => '123'], false],
    ['code only', ['phone' => '+91'], false],
    ['unknown code', ['phone' => '+999 1234567890'], false],
    ['invalid selected code', ['phone' => '9876543210', 'phone_country_code' => '+999'], false],
    ['array phone', ['phone' => ['9876543210']], false],
    ['array code', ['phone' => '9876543210', 'phone_country_code' => ['+91']], false],
    ['email trim', ['email' => ' person@example.com '], true],
    ['missing email', ['email' => null], false],
    ['email without domain suffix', ['email' => 'person@example'], false],
    ['multiple at signs', ['email' => 'person@@example.com'], false],
    ['email spaces', ['email' => 'per son@example.com'], false],
    ['array email', ['email' => ['person@example.com']], false],
];

foreach ($cases as [$label, $input, $expected]) {
    $data = LeadContactValidation::prepare(array_merge(['email' => 'person@example.com'], $input));
    $validator = Validator::make($data, LeadContactValidation::rules());
    checkLead($validator->passes() === $expected, "Validation failed: {$label} " . $validator->errors());
}
$data = LeadContactValidation::prepare(['phone' => '9100001234']);
checkLead($data['phone'] === '+91 9100001234', 'National digits were incorrectly removed');
checkLead(LeadContactValidation::prepare($data) === $data, 'Repeated normalization changed the number');

$controller = new class extends LeadContactController {
    protected function authorizeLeadAccess(string $action = 'view'): void {}
};
checkLead($controller->create()->getData()['countries']->isNotEmpty(), 'Lead create Country lookup failed');
checkLead($controller->edit(1)->getData()['lead']->id === 1, 'Lead edit Country lookup failed');

foreach (['store', 'update'] as $action) {
    $request = Illuminate\Http\Request::create('/leads/contacts', 'POST', [
        'contact_name' => 'Invalid lead', 'email' => 'bad-email', 'phone' => 'abc',
        'lead_source' => 'website', 'lead_owner_id' => 1,
    ]);
    $rejected = false;
    try {
        $action === 'store' ? $controller->store($request) : $controller->update($request, 1);
    } catch (Illuminate\Validation\ValidationException $exception) {
        $rejected = isset($exception->errors()['email'], $exception->errors()['phone']);
    }
    checkLead($rejected, "Lead {$action} did not reject invalid email and phone");
    checkLead(DB::table('lead_contacts')->count() === 1, "Lead {$action} changed records before validation");
}

$import = new LeadsImport;
$row = $import->prepareForValidation(['contact_name' => 'Import lead', 'email' => ' person@example.com ', 'phone' => '9876543210'], 2);
app(RowValidator::class)->validate([2 => $row], $import);
checkLead($import->model($row)->phone === '+91 9876543210', 'Imported phone normalization failed');
$numericRow = $import->prepareForValidation(['contact_name' => 'Numeric import', 'email' => 'person@example.com', 'phone' => 9876543210.0], 3);
app(RowValidator::class)->validate([3 => $numericRow], $import);
checkLead($import->model($numericRow)->phone === '+91 9876543210', 'Numeric spreadsheet phone rejected');
$row['phone'] = 'invalid phone';
$rejected = false;
try {
    app(RowValidator::class)->validate([2 => $row], $import);
} catch (Maatwebsite\Excel\Validators\ValidationException $exception) {
    $rejected = true;
}
checkLead($rejected, 'Import accepted an invalid phone');

echo 'PASS: ' . count($cases) . " validation cases, normalization, create/edit Country lookups, store/update rejection, and import validation.\n";
