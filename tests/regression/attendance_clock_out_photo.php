<?php

require __DIR__ . '/attendance_auto_clock_out.php';

use App\Http\Controllers\DashboardController;
use App\Models\{Attendance, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema, Storage};

config(['database.connections.central' => config('database.connections.tenant')]);
DB::purge('central');
Schema::connection('central')->create('companies', function ($t) { $t->id(); $t->string('db_name'); $t->softDeletes(); });
DB::connection('central')->table('companies')->insert(['id' => 1, 'db_name' => ':memory:']);
Schema::table('users', fn ($t) => $t->boolean('login_allowed')->default(true));
App\Services\WorkforceRecordSchema::ensure();
Storage::fake('local');
$dashboard = new DashboardController;
$image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aD1sAAAAASUVORK5CYII=';
$request = function (array $data) use ($app) {
    $r = Request::create('/dashboard/clock-out', 'POST', $data + ['clock_out_timezone' => 'Asia/Kolkata']);
    $r->setLaravelSession($app['session']->driver());
    $app->instance('request', $r);
    return $r;
};
foreach ([2, 3, 4] as $userId) {
    Auth::guard('web')->setUser(User::findOrFail($userId));
    Carbon::setTestNow(Carbon::parse('2026-10-12 18:00:00', 'Asia/Kolkata'));
    $session = $makeSession($userId, '2026-10-12', '09:00:00');
    try {
        $dashboard->clockOut($request([]));
        throw new RuntimeException('Manual clock-out accepted a missing photo.');
    } catch (Illuminate\Validation\ValidationException $e) {
        checkSave(isset($e->errors()['clock_out_selfie']), 'Missing clock-out photo error.');
    }
    try {
        $dashboard->clockOut($request(['clock_out_selfie' => 'data:image/png;base64,aW52YWxpZA==']));
        throw new RuntimeException('Invalid image accepted.');
    } catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
        checkSave($e->getStatusCode() === 422, 'Invalid image error status.');
    }
    checkSave($session->fresh()->clock_out === null, 'Rejected photo changed attendance.');
    $dashboard->clockOut($request(['clock_out_selfie' => $image, 'user_id' => 1]));
    $session = $session->fresh();
    checkSave($session->clock_out === '18:00:00' && $session->total_duration === '09:00:00', 'Manual clock-out duration incorrect.');
    checkSave(!$session->auto_clocked_out && $session->clock_out_photo, 'Manual photo or flag missing.');
    $path = $session->getRawOriginal('clock_out_photo');
    if ($userId !== 2) checkSave(Storage::disk('local')->exists($path), 'Authority photo missing from private storage.');
    $dashboard->clockOut($request([]));
    checkSave($session->fresh()->getRawOriginal('clock_out_photo') === $path, 'Repeated request replaced photo.');
    if ($userId === 2) unlink(public_path($path));
    $forgotten = $makeSession($userId, '2026-10-13', '09:00:00');
    Carbon::setTestNow(Carbon::parse('2026-10-13 23:58:00', 'Asia/Kolkata'));
    $dashboard->clockOut($request([]));
    checkSave($forgotten->fresh()->clock_out === '23:58:00' && $forgotten->fresh()->auto_clocked_out && !$forgotten->fresh()->clock_out_photo, 'Automatic clock-out required an image.');
}
Carbon::setTestNow();
echo "Clock-out photo checks passed for employee, HR and manager: required valid manual photos, correct durations, repeat protection and automatic closure without photos.\n";
