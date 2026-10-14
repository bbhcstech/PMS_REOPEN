<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Http\Request;

function timezoneCheck($condition, $message) { if (!$condition) throw new RuntimeException($message); }
config(['app.timezone' => 'Asia/Kolkata', 'database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
Schema::connection('tenant')->create('app_settings', function ($table) { $table->id(); $table->string('key'); $table->string('value'); });
DB::connection('tenant')->table('app_settings')->insert(['key' => 'loc_timezone', 'value' => 'UTC']);
(new App\Providers\AppServiceProvider($app))->boot();
timezoneCheck(config('app.timezone') === 'Asia/Kolkata' && date_default_timezone_get() === 'Asia/Kolkata', 'Tenant localization changed the deployment clock');
foreach ([App\Http\Controllers\DashboardController::class, App\Http\Controllers\AttendanceController::class] as $class) {
    $method = new ReflectionMethod($class, 'resolveEmployeeTimezone');
    foreach (['America/New_York', 'Asia/Dubai', 'UTC', null] as $browserTimezone) {
        timezoneCheck($method->invoke(new $class, $browserTimezone, 25.0, 55.0) === 'Asia/Kolkata', 'Attendance followed device/location timezone');
    }
}
Auth::guard('web')->setUser(new App\Models\User(['role' => 'admin']));
$controller = new App\Http\Controllers\Admin\Settings\LocalizationController;
timezoneCheck($controller->index()->getData()['settings']['timezone'] === 'Asia/Kolkata', 'Localization displayed an obsolete company timezone');
try {
    $controller->update(Request::create('/localization', 'POST', ['currency' => 'INR', 'currency_symbol' => 'Rs', 'currency_position' => 'left', 'timezone' => 'UTC', 'date_format' => 'Y-m-d', 'time_format' => 'h:i A', 'language' => 'en']));
    throw new RuntimeException('Localization allowed the deployment timezone to change');
} catch (Illuminate\Validation\ValidationException $e) {
    timezoneCheck(isset($e->errors()['timezone']), 'Wrong validation error');
}
foreach (['mysql', 'central', 'session_db', 'mariadb'] as $connection) timezoneCheck(config('database.connections.'.$connection.'.timezone') === '+05:30', 'Database clock not explicit: '.$connection);
echo "PASS: deployment clock ignores tenant settings, new attendance uses IST, localization rejects timezone overrides, and database sessions use IST.\n";
