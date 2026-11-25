<?php

// Reuse the isolated employee fixture; no live databases or real email.
require __DIR__.'/employee_exit_date_and_role.php';

use App\Models\{User, EmployeeDetail};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Notification, Schema};

foreach (['users' => new User, 'employee_details' => new EmployeeDetail] as $table => $model) {
    $missing = array_diff($model->getFillable(), Schema::getColumnListing($table));
    Schema::table($table, function ($blueprint) use ($missing) {
        foreach ($missing as $column) $blueprint->text($column)->nullable();
    });
}
Schema::create('parent_departments', function ($table) {
    $table->id(); $table->string('dpt_name');
});
DB::table('parent_departments')->insert(['id' => 1, 'dpt_name' => 'Engineering']);
$mailer = new class {
    public int $calls = 0;
    public ?int $transactionLevel = null;
    public bool $detailSaved = false;
    public function sendNow($user, $notification): void {
        $this->calls++;
        $this->transactionLevel = DB::connection('tenant')->transactionLevel();
        $this->detailSaved = EmployeeDetail::where('user_id', $user->id)->exists();
        throw new RuntimeException('Simulated SMTP timeout');
    }
};
Notification::swap($mailer);
$request = Request::create('/employees', 'POST', [
    'name' => 'New Employee', 'company_id' => 1, 'email' => 'new-employee@example.test',
    'mobile_country_code' => '+91', 'mobile' => '9234567890',
    'joining_date' => '2026-10-01', 'dob' => '1995-01-01', 'designation_id' => 1,
    'parent_dpt_id' => 1, 'employee_id_option' => 'custom', 'employee_id' => 'NEW-001',
    'user_role' => 'employee',
]);
$response = $controller->store($request, app(App\Services\GovernmentIdDobVerifier::class));
$employee = User::where('email', 'new-employee@example.test')->first();
if (!$employee || !$response->isRedirection() || $mailer->calls !== 1 || $mailer->transactionLevel !== 0 || !$mailer->detailSaved) {
    throw new RuntimeException('Employee was not saved/committed before SMTP delivery.');
}
if (!EmployeeDetail::where('user_id', $employee->id)->exists() || DB::connection('tenant')->transactionLevel() !== 0) {
    throw new RuntimeException('Failed SMTP removed employee records or left transaction open.');
}
echo "PASS: employee creation commits before email, releases locks, and preserves saved employee when SMTP fails. No real email sent.\n";
