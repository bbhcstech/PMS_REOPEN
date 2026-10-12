<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('tenant');

        if ($schema->hasTable('attendance_settings')) {
            $columns = [
                'clock_window_enabled' => fn (Blueprint $t) => $t->boolean('clock_window_enabled')->default(false),
                'clock_window_enabled_at' => fn (Blueprint $t) => $t->date('clock_window_enabled_at')->nullable(),
                'clock_in_opens_at' => fn (Blueprint $t) => $t->time('clock_in_opens_at')->nullable(),
                'clock_in_closes_at' => fn (Blueprint $t) => $t->time('clock_in_closes_at')->nullable(),
                'half_day_after_time' => fn (Blueprint $t) => $t->time('half_day_after_time')->nullable(),
                'office_end_time' => fn (Blueprint $t) => $t->time('office_end_time')->nullable(),
                'clock_out_closes_at' => fn (Blueprint $t) => $t->time('clock_out_closes_at')->nullable(),
                'missed_clock_out_status' => fn (Blueprint $t) => $t->string('missed_clock_out_status', 20)->default('half_day'),
            ];
            foreach ($columns as $column => $define) {
                if (! $schema->hasColumn('attendance_settings', $column)) {
                    $schema->table('attendance_settings', $define);
                }
            }
        }

        // Why the system set a status (missed_clock_in / missed_clock_out), so it can be told apart from manual edits.
        if ($schema->hasTable('attendances') && ! $schema->hasColumn('attendances', 'system_status_reason')) {
            $schema->table('attendances', fn (Blueprint $t) => $t->string('system_status_reason', 30)->nullable());
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('tenant');
        foreach (['clock_window_enabled', 'clock_window_enabled_at', 'clock_in_opens_at', 'clock_in_closes_at', 'half_day_after_time', 'office_end_time', 'clock_out_closes_at', 'missed_clock_out_status'] as $column) {
            if ($schema->hasTable('attendance_settings') && $schema->hasColumn('attendance_settings', $column)) {
                $schema->table('attendance_settings', fn (Blueprint $t) => $t->dropColumn($column));
            }
        }
        if ($schema->hasTable('attendances') && $schema->hasColumn('attendances', 'system_status_reason')) {
            $schema->table('attendances', fn (Blueprint $t) => $t->dropColumn('system_status_reason'));
        }
    }
};
