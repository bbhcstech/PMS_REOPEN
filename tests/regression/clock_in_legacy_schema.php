<?php

// An upgraded company with the original attendance table and no selfie/location migration.
require __DIR__.'/attendance_clock_out_photo.php';

use App\Models\{Attendance, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth, DB, Schema};

Schema::create('employee_details', function ($table) { $table->id(); $table->unsignedBigInteger('user_id'); });
Auth::guard('web')->setUser(User::findOrFail(2));
Carbon::setTestNow(Carbon::parse('2026-10-14 09:00:00', 'Asia/Kolkata'));
$makeRequest = function (array $data) use ($request) {
    $r = $request($data); $r->headers->set('Accept', 'application/json'); return $r;
};
try {
    Schema::table('attendances', fn ($table) => $table->dropColumn(['clock_in_latitude', 'clock_in_longitude', 'clock_in_photo', 'clock_in_address', 'work_from_type']));
    Schema::connection('central')->create('attendances', fn ($table) => $table->id());
    DB::connection('central')->table('attendances')->insert(['id' => 99]);
    DB::listen(function ($query) {
        if (preg_match('/^\s*alter\s/i', $query->sql)) checkSave($query->connection->transactionLevel() === 0, 'Clock schema upgrade ran inside an attendance transaction.');
    });
    // Legacy tenants can also retain working_from NOT NULL without a default.
    DB::table('attendances')->whereNull('working_from')->update(['working_from' => 'office']);
    Schema::table('attendances', fn ($table) => $table->string('working_from')->nullable(false)->change());
    $response = $dashboard->clockIn($makeRequest(['clock_in_selfie' => $image, 'clock_in_timezone' => 'Asia/Kolkata']));
    checkSave($response->getStatusCode() === 200 && $response->getData()->success, 'Legacy tenant clock-in was not confirmed.');
    $line = Attendance::where('user_id', 2)->whereDate('date', '2026-10-14')->firstOrFail();
    $inPhoto = $line->getRawOriginal('clock_in_photo');
    checkSave($line->clock_in === '09:00:00' && !$line->clock_out && $line->working_from === 'office', 'Clock-in did not start an open shift.');
    Carbon::setTestNow(Carbon::parse('2026-10-14 18:00:00', 'Asia/Kolkata'));
    $out = $dashboard->clockOut($makeRequest(['clock_out_selfie' => $image]));
    $line->refresh();
    checkSave($out->getStatusCode() === 200 && $line->clock_out === '18:00:00' && $line->total_duration === '09:00:00', 'Clock-out did not complete the same legacy attendance record.');
    $before = DB::table('attendances')->where('id', $line->id)->first();
    App\Services\WorkforceRecordSchema::ensure();
    checkSave($before == DB::table('attendances')->where('id', $line->id)->first(), 'Repeat schema check changed attendance history.');
    checkSave(!Schema::connection('central')->hasColumn('attendances', 'clock_in_photo') && DB::connection('central')->table('attendances')->value('id') === 99, 'Repair touched the central database.');
    foreach ([$inPhoto, $line->getRawOriginal('clock_out_photo')] as $path) if ($path && is_file(public_path($path))) unlink(public_path($path));
    Carbon::setTestNow();
    echo "PASS: partially upgraded tenant clock-in repairs missing photo/location fields, supplies required legacy working_from, and completes the same shift on clock-out.\n";
} catch (Throwable $e) {
    fwrite(STDERR, get_class($e).': '.$e->getMessage()."\n"); exit(1);
}
