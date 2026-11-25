<?php

namespace App\Console\Commands;

use App\Models\Central\Company;
use App\Services\AttendanceAutoClockOut;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoClockOutAttendance extends Command
{
    protected $signature = 'attendance:auto-clock-out';
    protected $description = 'Close forgotten attendance sessions at 23:58 in each session timezone across company databases.';

    public function handle(AttendanceAutoClockOut $service): int
    {
        $originalConnection = config('database.connections.tenant');
        $originalTimezone = config('app.timezone');
        $failures = 0;
        try {
            foreach (Company::whereNotNull('db_name')->get() as $company) {
                try {
                    $connection = DB::connection('mysql')->getConfig();
                    $connection['url'] = null;
                    $connection['database'] = $company->db_name;
                    config(['database.connections.tenant' => $connection]);
                    DB::purge('tenant');
                    \App\Services\AttendanceClockPolicy::forget();
                    $count = $service->closeDue((int) $company->id);
                    $absent = \App\Services\AttendanceClockPolicy::markMissedClockIns((int) $company->id, null, false);
                    $this->line("Company {$company->id}: {$count} session(s) closed, {$absent} missed clock-in(s) marked absent.");
                } catch (\Throwable $e) {
                    $failures++;
                    report($e);
                    $this->error("Company {$company->id}: automatic clock-out failed; see application log.");
                }
            }
        } finally {
            \App\Services\AttendanceClockPolicy::forget();
            config(['database.connections.tenant' => $originalConnection, 'app.timezone' => $originalTimezone]);
            DB::purge('tenant');
        }
        return $failures ? self::FAILURE : self::SUCCESS;
    }
}
