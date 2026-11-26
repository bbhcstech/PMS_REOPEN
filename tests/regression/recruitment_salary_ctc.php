<?php

// Run with: php tests/regression/recruitment_salary_ctc.php  (isolated SQLite; no live data)
// Recruitment "Salary Range (CTC)": numbers only (min/max, up to 2 decimals), stored and shown in LPA.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\RecruitmentController;
use App\Models\{RecruitmentRequirement, User};
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Validation\ValidationException;

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Schema::create('users', function ($t) { $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->string('name'); $t->string('email')->nullable(); $t->string('password')->nullable(); $t->string('role')->nullable(); $t->boolean('is_active')->default(true); $t->timestamps(); });
Schema::create('departments', function ($t) { $t->id(); $t->string('dpt_name')->nullable(); $t->timestamps(); });
Schema::create('recruitment_requirements', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->string('title'); $t->unsignedBigInteger('department_id')->nullable(); $t->string('department_name')->nullable();
    $t->integer('positions')->default(1); $t->string('employment_type')->nullable(); $t->string('experience_required')->nullable(); $t->string('salary_range')->nullable();
    $t->string('location')->nullable(); $t->text('description')->nullable(); $t->text('requirements_summary')->nullable(); $t->string('status')->nullable();
    $t->unsignedBigInteger('created_by')->nullable(); $t->string('pdf_header_image')->nullable(); $t->string('pdf_footer_image')->nullable(); $t->timestamps();
});
(require database_path('migrations/tenant/2026_11_26_000002_create_recruitment_branding_assets.php'))->up();
Schema::create('notifications', function ($t) { $t->uuid('id')->primary(); $t->string('type'); $t->string('notifiable_type'); $t->unsignedBigInteger('notifiable_id'); $t->text('data'); $t->timestamp('read_at')->nullable(); $t->timestamps(); });
DB::table('users')->insert(['id' => 1, 'company_id' => 1, 'name' => 'Admin', 'role' => 'admin']);
Auth::guard('web')->setUser(User::findOrFail(1));
session(['current_company_id' => 1]);

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};
Schema::create('app_settings', function ($t) { $t->id(); $t->string('key'); $t->text('value')->nullable(); $t->timestamps(); });
$policy = (new ReflectionMethod(RecruitmentController::class, 'generatePolicyCard'))->invoke(new RecruitmentController);
$save = function (array $salary) {
    $request = Request::create('/admin/hr/recruitment', 'POST', ['title' => 'Unit Tester', 'positions' => 1, 'employment_type' => 'Full-time'] + $salary, [], [
        'pdf_header_image' => UploadedFile::fake()->image('header.png', 1240, 160),
        'pdf_footer_image' => UploadedFile::fake()->image('footer.png', 1240, 140),
    ]);
    $request->setLaravelSession(app('session')->driver());
    app()->instance('request', $request);
    try {
        (new RecruitmentController)->store($request);
    } catch (ValidationException $e) {
        return $e->errors();
    }
    return RecruitmentRequirement::latest('id')->first()->salary_range;
};

$check($save(['salary_min' => '4.5', 'salary_max' => '6']) === '4.5 - 6 LPA', 'a range is stored as "4.5 - 6 LPA"');
$check($save(['salary_min' => '12', 'salary_max' => '12.00']) === '12 LPA', 'equal min and max are stored as a single "12 LPA"');
$check($save(['salary_min' => '8']) === '8 LPA', 'a single figure is stored as "8 LPA"');
$check($save([]) === null, 'leaving the CTC empty keeps it negotiable');
$errors = $save(['salary_min' => '60,000', 'salary_max' => '80,000']);
$check(is_array($errors) && isset($errors['salary_min']), 'text such as "60,000" is rejected (numbers only)');
$errors = $save(['salary_min' => '$5']);
$check(is_array($errors) && isset($errors['salary_min']), 'currency symbols are rejected');
$errors = $save(['salary_min' => '9', 'salary_max' => '6']);
$check(is_array($errors) && isset($errors['salary_max']), 'a maximum below the minimum is rejected');
$errors = $save(['salary_max' => '6']);
$check(is_array($errors) && isset($errors['salary_min']), 'a maximum without a minimum asks for the minimum');

// PDF shows the CTC with its LPA unit
$requirement = RecruitmentRequirement::where('salary_range', '4.5 - 6 LPA')->firstOrFail();
$html = view('admin.recruitment.pdf', ['requirement' => $requirement, 'policyCard' => $policy])->render();
$check(str_contains($html, 'Salary Range (CTC)') && str_contains($html, '4.5 - 6 LPA'), 'PDF shows "Salary Range (CTC)" with the LPA value');
$requirement->salary_range = '7 - 9';
$html = view('admin.recruitment.pdf', ['requirement' => $requirement, 'policyCard' => $policy])->render();
$check(str_contains($html, '7 - 9 LPA'), 'bare figures in the PDF get the LPA unit');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: Salary Range (CTC) takes numbers only and is shown in LPA.\n";
exit($failures ? 1 : 0);
