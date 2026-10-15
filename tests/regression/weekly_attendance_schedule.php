<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{AppSetting, User, Attendance, AttendanceSetting};
use App\Services\{WorkScheduleService, AttendanceCalendar};
use App\Http\Controllers\Admin\Settings\WorkScheduleController;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Http\Request;
use Carbon\Carbon;

app()->instance('db.connector.sqlite', new class implements Illuminate\Database\Connectors\ConnectorInterface {
    private array $databases = [];
    public function connect(array $config) { return $this->databases[$config['database']] ??= new PDO('sqlite::memory:'); }
});
function weeklyTenant($name) {
    config(['database.default' => 'tenant', 'database.connections.tenant' => ['driver' => 'sqlite', 'database' => $name, 'prefix' => '']]);
    DB::purge('tenant');
}
function weeklyCheck($ok, $message) { if (!$ok) throw new RuntimeException($message); }
foreach (['alpha', 'beta'] as $name) {
    weeklyTenant($name);
    Schema::create('app_settings', function ($t) {
        $t->id(); $t->string('key')->unique(); $t->text('value'); foreach (['label', 'page', 'section', 'type'] as $column) $t->string($column)->nullable(); $t->timestamps();
    });
    Schema::create('users', function ($t) { $t->id(); $t->integer('company_id'); $t->string('name'); $t->string('role'); $t->boolean('is_active')->default(true); });
    Schema::create('attendances', function ($t) {
        $t->id(); $t->integer('user_id'); $t->integer('company_id'); $t->date('date'); $t->string('status'); $t->string('work_from_type')->nullable();
        foreach (['clock_in', 'clock_out', 'late', 'half_day'] as $column) $t->string($column)->nullable(); $t->timestamps();
    });
    DB::table('users')->insert(['id' => 1, 'company_id' => 1, 'name' => 'Admin', 'role' => 'admin']);
    DB::table('users')->insert(['id' => 2, 'company_id' => 1, 'name' => 'Employee', 'role' => 'employee']);
}
weeklyTenant('alpha');
Auth::guard('web')->setUser(User::find(1));
config(['session.driver' => 'array']);
$controller = new WorkScheduleController();
$modes = array_fill_keys(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'office') + ['Saturday' => 'wfh', 'Sunday' => 'weekend'];
$data = ['weekly_modes' => $modes, 'work_start_time' => '09:00', 'work_end_time' => '18:00', 'break_duration' => 60];
$save = fn ($data) => $controller->update(Request::create('/work-schedule', 'POST', $data));
$save($data);
$settings = WorkScheduleService::getSettings();
weeklyCheck(count($settings['working_days']) === 6 && $settings['weekly_wfh_days'] === ['Saturday'], 'Weekly preset not persisted.');
weeklyCheck(WorkScheduleService::isUserWfhOnDate(2, '2026-10-03')['type'] === 'weekly_schedule', 'Saturday WFH missing.');
weeklyCheck(WorkScheduleService::isUserWfhOnDate(2, '2026-10-04') === null, 'Sunday became WFH.');
Carbon::setTestNow(Carbon::parse('2026-10-15 12:00:00'));
$build = fn ($records = null, $schedule = null, $detail = null) => (new AttendanceCalendar())->build(Carbon::parse('2026-10-01'), $records ?? collect(), collect(), collect(), $detail,
    $settings['working_days'], 2, $schedule ?? $settings);
$calendar = $build(); $days = collect($calendar['days'])->keyBy('key');
weeklyCheck($calendar['totals']['wfh'] === 5 && $calendar['totals']['weekend'] === 4 && $calendar['totals']['day_off'] === 0, 'October must have five WFH Saturdays and four Sundays counted separately from short shifts.');
weeklyCheck($days['2026-10-03']['status'] === 'absent' && $days['2026-10-03']['wfh'], 'Scheduled WFH fabricated presence or lost mode.');
weeklyCheck($days['2026-10-31']['status'] === 'not_marked' && $days['2026-10-31']['wfh'], 'Future WFH schedule missing or counted as present.');
weeklyCheck((float) $calendar['totals']['attendance_rate'] === 0.0 && $calendar['totals']['seconds'] === 0, 'Schedule invented attendance/hours.');
$attendance = Attendance::create(['company_id' => 1, 'user_id' => 2, 'date' => '2026-10-03', 'clock_in' => '09:00:00', 'clock_out' => '18:00:00', 'status' => 'present', 'work_from_type' => 'office']);
WorkScheduleService::applyOrganizationAttendanceRules($attendance, new AttendanceSetting());
weeklyCheck($attendance->fresh()->status === 'wfh' && $attendance->fresh()->work_from_type === 'wfh', 'Recorded Saturday shift not saved as WFH.');
$recorded = $build(collect([$attendance->fresh()]));
weeklyCheck($recorded['totals']['wfh'] === 5 && $recorded['totals']['wfh_present'] === 1 && $recorded['totals']['attendance_rate'] > 0, 'WFH double-counted or real attendance ignored.');
$holidaySchedule = $settings; $holidaySchedule['special_days'] = [['date' => '2026-10-10', 'type' => 'holiday', 'title' => 'Company holiday']];
$holidayDays = collect($build(null, $holidaySchedule)['days'])->keyBy('key');
weeklyCheck($holidayDays['2026-10-10']['status'] === 'holiday' && !$holidayDays['2026-10-10']['wfh'], 'Holiday did not override recurring WFH.');
$joined = $build(null, null, (object) ['joining_date' => '2026-10-12']);
weeklyCheck($joined['totals']['wfh'] === 3, 'WFH before joining counted.');
$before = AppSetting::pluck('value', 'key')->all();
foreach ([['weekly_modes' => array_fill_keys(array_keys($modes), 'weekend')], ['weekly_modes' => array_replace($modes, ['Saturday' => 'bad'])], ['break_duration' => -1]] as $bad) {
    try { $save(array_replace($data, $bad)); throw new RuntimeException('Invalid schedule accepted.'); }
    catch (Illuminate\Validation\ValidationException $e) { weeklyCheck(count($e->errors()) > 0, 'Validation missing.'); }
}
weeklyCheck(AppSetting::pluck('value', 'key')->all() === $before, 'Invalid schedule changed settings.');
$custom = array_replace($modes, ['Friday' => 'weekend', 'Saturday' => 'office', 'Sunday' => 'wfh']);
$save(array_replace($data, ['weekly_modes' => $custom]));
weeklyCheck(WorkScheduleService::isUserWfhOnDate(2, '2026-10-04') !== null && WorkScheduleService::isUserWfhOnDate(2, '2026-10-03') === null, 'Custom WFH day was hard-coded to Saturday.');
weeklyCheck(!in_array('Friday', WorkScheduleService::getSettings()['working_days'], true), 'Custom weekend was not saved.');
$save($data);
Auth::guard('web')->setUser(new User(['role' => 'hr', 'company_id' => 1]));
try { $save($data); throw new RuntimeException('HR changed company schedule.'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { weeklyCheck($e->getStatusCode() === 403, 'Incorrect access result.'); }
weeklyTenant('beta');
weeklyCheck(WorkScheduleService::getSettings()['weekly_wfh_days'] === [], 'Schedule leaked to another company.');
weeklyCheck(count(WorkScheduleService::getSettings()['working_days']) === 5, 'Other company working days changed.');
weeklyTenant('alpha');
weeklyCheck(WorkScheduleService::getSettings()['weekly_wfh_days'] === ['Saturday'], 'Saved schedule lost on reload.');
Carbon::setTestNow();
echo "PASS: weekly schedules, Saturday WFH/Sunday off, real attendance storage, totals without fabricated presence, holidays, joining dates, validation, admin permissions and tenant isolation.\n";
