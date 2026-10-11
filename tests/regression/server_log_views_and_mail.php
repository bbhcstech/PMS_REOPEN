<?php

require __DIR__.'/attendance_location.php';
use Illuminate\Support\Facades\{DB, Schema};

app('view')->share('errors', new Illuminate\Support\ViewErrorBag);
$data = [
    'attendance' => null,
    'employee' => (object) ['name' => 'Employee', 'designation' => null, 'department' => null],
    'userId' => 1, 'date' => '2026-10-12',
    'location' => collect([(object) ['id' => 2, 'location' => 'Office']]),
    'departments' => collect(),
];
$html = view('admin.attendance.edit_form', $data)->render();
if (! str_contains($html, 'clock_in_address')) throw new RuntimeException('Missing attendance create form.');
$data['attendance'] = (new App\Models\Attendance)->forceFill(['id' => 4, 'location_id' => 2, 'clock_in_address' => 'Saved address']);
if (! str_contains(view('admin.attendance.edit_form', $data)->render(), 'Saved address')) throw new RuntimeException('Existing attendance address lost.');
$notification = new App\Notifications\TicketCreatedNotification((object) ['id' => 1, 'subject' => 'Support request', 'priority' => 'high']);
$message = $notification->toMail((object) ['name' => 'Employee']);
$html = app(Illuminate\Mail\Markdown::class)->render($message->markdown, $message->data())->toHtml();
if (! str_contains($html, 'Support request') || ! str_contains($html, 'View Ticket')) throw new RuntimeException('Ticket email content lost.');

DB::setDefaultConnection('tenant');
Schema::create('tasks', function ($t) { $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('project_id')->nullable(); });
Schema::create('companies', function ($t) { $t->id(); $t->string('company_name'); });
Schema::create('projects', function ($t) { $t->id(); $t->string('name'); });
DB::table('companies')->insert(['id' => 5, 'company_name' => 'Workspace company']);
DB::table('tasks')->insert(['company_id' => 5]);
$controller = new App\Http\Controllers\DeveloperPortalController;
$queryMethod = new ReflectionMethod($controller, 'developerTasksQuery');
$task = $queryMethod->invoke($controller)->first();
if ($task->company_name !== 'Workspace company') throw new RuntimeException('Legacy tenant company name missing.');
Schema::table('companies', fn ($t) => $t->renameColumn('company_name', 'name'));
if ($queryMethod->invoke($controller)->first()->company_name !== 'Workspace company') throw new RuntimeException('Modern company name missing.');
Schema::drop('companies');
Schema::drop('projects');
if ($queryMethod->invoke($controller)->first()->company_name !== null) throw new RuntimeException('Optional metadata failed.');
Schema::create('employee_details', function ($t) {
    $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('user_role')->nullable(); $t->timestamps();
});
$detail = App\Models\EmployeeDetail::create(['user_id' => 1, 'user_role' => 'employee']);
if ($detail->fresh()->user_role !== null) throw new RuntimeException('Built-in role was written into an integer role ID.');
$detail->update(['user_role' => 12]);
if ((int) $detail->fresh()->user_role !== 12) throw new RuntimeException('Valid numeric employee role ID was changed.');
// Reproduce the unavailable fallback database without reaching any real database.
DB::purge('tenant');
app()->instance('db.connector.sqlite', new class implements Illuminate\Database\Connectors\ConnectorInterface {
    public function connect(array $config) { throw new PDOException('Simulated database access denied'); }
});
$countries = App\Models\Country::getAllWithPhoneCodes();
if ($countries->count() < 100 || ! $countries->firstWhere('name', 'India')?->phone_code) throw new RuntimeException('Country picker failed during database outage.');
echo "PASS: attendance create/edit forms, rendered ticket email, and developer task metadata on legacy/modern/minimal schemas.\n";
echo "PASS: country picker retains dial codes when its database is unavailable.\n";
