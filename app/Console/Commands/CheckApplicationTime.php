<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckApplicationTime extends Command
{
    protected $signature = 'app:time-check';
    protected $description = 'Read-only check of application, PHP and database clocks for the IST deployment.';

    public function handle(): int
    {
        $this->line('Application timezone: '.config('app.timezone'));
        $this->line('PHP timezone: '.date_default_timezone_get());
        $this->line('Application now: '.now()->format('Y-m-d H:i:s P'));
        $failed = config('app.timezone') !== 'Asia/Kolkata' || date_default_timezone_get() !== 'Asia/Kolkata';
        $rows = [];
        foreach (['mysql', 'central', 'tenant', 'session_db'] as $name) {
            try {
                $clock = DB::connection($name)->selectOne('SELECT NOW() AS local_now, UTC_TIMESTAMP() AS utc_now, @@session.time_zone AS session_timezone');
                $rows[] = [$name, $clock->session_timezone, $clock->local_now, $clock->utc_now];
                $failed = $failed || $clock->session_timezone !== '+05:30';
                $databaseTime = \Carbon\Carbon::parse($clock->local_now, 'Asia/Kolkata');
                $failed = $failed || abs($databaseTime->getTimestamp() - time()) > 5;
            } catch (\Throwable $e) {
                $rows[] = [$name, 'Unavailable', '-', '-'];
                $failed = true;
            }
        }
        $this->table(['Connection', 'Session timezone', 'Database now', 'Database UTC'], $rows);
        if ($failed) {
            $this->error('Clock configuration or connectivity needs attention. Check APP_TIMEZONE, DB_TIMEZONE, configuration cache and server clock synchronization.');
            return self::FAILURE;
        }
        $this->info('PASS: PHP, application and database sessions agree on IST. No records were modified.');
        return self::SUCCESS;
    }
}
