<?php

// Run with: php tests/regression/company_ai_live_context.php  (isolated SQLite; no live data)
// Optional real provider check with synthetic data only: RUN_LIVE_AI=1 php tests/regression/company_ai_live_context.php
// Company assistant live knowledge: question topics, holidays/weekly offs per week and month, office timings,
// permission gating of personal sections, company isolation, workspace guide and the grounded provider payload.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Central\Company;
use App\Models\User;
use App\Services\{CompanyAiLiveContext, GroundedAiResponse};
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Http, Schema};

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant']);
$schema = Schema::connection('tenant');
$schema->create('holidays', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->string('title')->nullable(); $t->string('occassion')->nullable();
    $t->date('date'); $t->string('type')->nullable(); $t->timestamp('archived_at')->nullable(); $t->timestamps();
});
$schema->create('app_settings', function ($t) { $t->id(); $t->string('key'); $t->text('value')->nullable(); $t->timestamps(); });
$schema->create('attendance_settings', function ($t) {
    $t->id(); $t->time('office_start_time')->nullable(); $t->time('late_time')->nullable(); $t->integer('half_day_threshold_minutes')->nullable();
    $t->integer('day_off_threshold_minutes')->nullable(); $t->boolean('clock_window_enabled')->default(false); $t->time('clock_in_opens_at')->nullable();
    $t->time('clock_in_closes_at')->nullable(); $t->time('half_day_after_time')->nullable(); $t->time('office_end_time')->nullable(); $t->timestamps();
});
$schema->create('users', function ($t) { $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->string('name'); $t->string('email')->nullable(); $t->string('role')->nullable(); $t->timestamp('archived_at')->nullable(); $t->timestamps(); });
$schema->create('employee_details', function ($t) { $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('department_id')->nullable(); $t->unsignedBigInteger('designation_id')->nullable(); $t->unsignedBigInteger('reporting_to')->nullable(); $t->date('joining_date')->nullable(); $t->string('employee_id')->nullable(); $t->timestamps(); });

Carbon::setTestNow(Carbon::parse('2026-11-26 10:00:00')); // Thursday
DB::table('holidays')->insert([
    ['company_id' => 1, 'title' => 'Guru Nanak Jayanti', 'occassion' => 'Guru Nanak Jayanti', 'date' => '2026-11-27'],
    ['company_id' => 1, 'title' => 'Christmas', 'occassion' => 'Christmas', 'date' => '2026-12-25'],
    ['company_id' => 1, 'title' => 'Last week holiday', 'occassion' => 'Last week holiday', 'date' => '2026-11-20'],
    ['company_id' => 2, 'title' => 'Other company secret holiday', 'occassion' => 'Other company secret holiday', 'date' => '2026-11-25'],
]);
DB::table('app_settings')->insert([
    ['key' => 'work_working_days', 'value' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])],
    ['key' => 'work_start_time', 'value' => '09:30'], ['key' => 'work_end_time', 'value' => '18:30'],
    ['key' => 'work_special_days', 'value' => json_encode([['type' => 'holiday', 'date' => '2026-11-24', 'title' => 'Company foundation day']])],
]);
DB::table('attendance_settings')->insert(['office_start_time' => '09:30:00', 'late_time' => '09:45:00', 'half_day_threshold_minutes' => 480, 'day_off_threshold_minutes' => 240]);
DB::table('users')->insert([['id' => 7, 'company_id' => 1, 'name' => 'Riya Sen', 'role' => 'employee'], ['id' => 8, 'company_id' => 1, 'name' => 'Amit Roy', 'role' => 'manager']]);
DB::table('employee_details')->insert([['user_id' => 7, 'reporting_to' => 8, 'employee_id' => 'EMP-7'], ['user_id' => 8, 'reporting_to' => null, 'employee_id' => 'EMP-8']]);

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};

$company = (new Company)->forceFill(['id' => 1, 'name' => 'Bengal IT Hub']);
$allowed = ['attendance' => false, 'leaves' => false, 'events' => false, 'tasks' => false, 'holidays' => true, 'dashboard' => true];
$user = new class extends User {
    public array $modules = [];
    public function canViewModule(string $moduleSlug): bool { return $this->modules[$moduleSlug] ?? false; }
};
$user->modules = $allowed;
$user->setConnection('tenant');
$user->forceFill(['id' => 7, 'company_id' => 1, 'name' => 'Riya Sen', 'role' => 'employee'])->syncOriginal();
$user->exists = true;
$detail = App\Models\EmployeeDetail::where('user_id', 7)->first();
$detail->setRelation('designation', (new App\Models\Designation)->forceFill(['name' => 'Developer']));
$detail->setRelation('department', null);
$detail->setRelation('reportingTo', User::find(8));
$user->setRelation('employeeDetail', $detail);
$live = app(CompanyAiLiveContext::class);

// 1. Topic detection
$check(in_array('holidays', $live->topics('how many holiday present in this week'), true), 'the reported question ("how many holiday present in this week") is understood as a holiday question');
$check(in_array('holidays', $live->topics('Kal chutti hai kya?'), true), 'Hindi/Bengali leave/holiday words are recognized');
$check(in_array('schedule', $live->topics('What are the office timings?'), true), 'office timing questions map to the schedule');
$check(in_array('leaves', $live->topics('What is my leave balance?'), true), 'leave balance questions map to leave');
$check($live->topics('explain quantum physics theory') === [], 'unrelated questions have no workspace topic (never sent to the provider)');
$check($live->topics('what about this week') === [], 'time words alone do not start a lookup');

// 2. Holiday facts (same rules as the attendance calendar: holidays table + Work Schedule special days + weekly offs)
$records = $live->build($company, $user, ['holidays']);
$byId = array_column($records, null, 'id');
$holidays = $byId['live:holidays']['facts'] ?? [];
$check(isset($byId['live:today']) && str_contains($byId['live:today']['facts']['today'], 'Thursday, 26 Nov 2026'), 'today record gives the current date');
$check(($byId['live:today']['facts']['this_week'] ?? '') === 'Mon, 23 Nov 2026 to Sun, 29 Nov 2026', 'current week range is resolved');
$check(($holidays['holidays_this_week_count'] ?? null) === 2, 'this week has 2 holidays (special day + Guru Nanak Jayanti)');
$check(json_encode($holidays['holidays_this_week'] ?? []) === json_encode(['Tue, 24 Nov 2026 — Company foundation day', 'Fri, 27 Nov 2026 — Guru Nanak Jayanti']), 'this week\'s holidays are listed with dates and names');
$check(($holidays['weekly_offs_this_week'] ?? null) === ['Sat, 28 Nov 2026', 'Sun, 29 Nov 2026'], 'weekly offs are listed separately from holidays');
$check(($holidays['holidays_next_week_count'] ?? null) === 0 && ($holidays['holidays_next_week'] ?? null) === 'None', 'a week without holidays is reported as none');
$check(($holidays['holidays_next_month_count'] ?? null) === 1, 'next month counts Christmas');
$check(($holidays['weekly_off_days'] ?? null) === 'Saturday, Sunday', 'weekly off days come from the work schedule');
$check(! str_contains(json_encode($records), 'Other company secret holiday'), 'another company\'s holidays never appear');
$check(isset($byId['live:schedule']), 'holiday answers also carry the office schedule');

// 3. Office timings and attendance policy
$schedule = $byId['live:schedule']['facts'] ?? [];
$check(($schedule['office_hours'] ?? null) === '09:30 AM to 06:30 PM', 'office hours from Work Schedule');
$check(($schedule['marked_late_after'] ?? null) === '09:45 AM', 'late cutoff from the attendance policy');

// 4. Permission gating: personal sections need the module permission
$records = $live->build($company, $user, ['leaves', 'attendance', 'events', 'work', 'payslips']);
$ids = array_column($records, 'id');
$check(! array_intersect($ids, ['live:leaves', 'live:attendance', 'live:events', 'live:work', 'live:payslips']), 'leave, attendance, events, tasks and payslips stay hidden without module permission');

// 5. Team + guide
$records = $live->build($company, $user, ['team', 'guide']);
$byId = array_column($records, null, 'id');
$check(($byId['live:team']['facts']['your_manager'] ?? '') === 'Amit Roy', 'reporting manager is answered from the employee record');
$check(isset($byId['live:guide']) && ! str_contains(json_encode($byId['live:guide']), 'Payroll'), 'workspace guide lists only pages the user can open');
$check($live->build($company, $user, []) === [], 'no topic -> no records');

// 6. Grounded provider payload carries live facts, history and the improved company prompt
config(['company_ai.api_key' => 'fake-test-key']);
Http::preventStrayRequests();
Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode(['in_scope' => true, 'answer' => 'There are 2 holidays this week.', 'source_ids' => ['live:holidays', 'live:today']])]]]])]);
$records = $live->build($company, $user, $live->topics('how many holiday present in this week'));
$result = app(GroundedAiResponse::class)->answer('how many holiday present in this week', $records, 'Bengal IT Hub Assistant', 'company', [['question' => 'hi', 'answer' => 'Hello']]);
$check($result['answer'] === 'There are 2 holidays this week.' && array_column($result['sources'], 'label') === ['Holidays & weekly offs', 'Today & you'], 'answer returns with readable source labels');
$sent = Http::recorded()->first()[0]->data();
$payload = json_decode($sent['messages'][1]['content'], true);
$check(isset($payload['recent_conversation']) && str_contains($sent['messages'][0]['content'], 'live:today'), 'provider receives conversation memory and the live-data instructions');
$check(collect($payload['authorized_company_records'])->contains(fn ($record) => $record['id'] === 'live:holidays' && $record['facts']['holidays_this_week_count'] === 2), 'provider receives the computed holiday count');

if (getenv('RUN_LIVE_AI')) {
    // Synthetic data only. Uses the configured provider key from .env.
    config(['company_ai.api_key' => env('GROQ_API_KEY')]);
    Http::swap(new Illuminate\Http\Client\Factory);
    foreach (['how many holiday present in this week', 'what are the office timings?', 'who is my manager?', 'Kal chutti hai kya?', 'any holiday next week?'] as $question) {
        $records = $live->build($company, $user, $live->topics($question));
        $answer = app(GroundedAiResponse::class)->answer($question, $records, 'Bengal IT Hub Assistant', 'company');
        echo "\n  Q: {$question}\n  A: " . str_replace("\n", "\n     ", $answer['answer']) . "\n";
    }
}

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: assistant live knowledge (holidays, timings, permissions, isolation, guide, grounded payload).\n";
exit($failures ? 1 : 0);
