<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_settings', 'half_day_threshold_minutes')) {
                $table->unsignedSmallInteger('half_day_threshold_minutes')->default(510)->after('late_time');
            }

            if (! Schema::hasColumn('attendance_settings', 'day_off_threshold_minutes')) {
                $table->unsignedSmallInteger('day_off_threshold_minutes')->default(270)->after('half_day_threshold_minutes');
            }
        });

        DB::table('attendance_settings')->update([
            'office_start_time' => '09:30:00',
            'late_time' => '09:30:00',
            'half_day_threshold_minutes' => 510,
            'day_off_threshold_minutes' => 270,
        ]);
    }

    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_settings', 'day_off_threshold_minutes')) {
                $table->dropColumn('day_off_threshold_minutes');
            }

            if (Schema::hasColumn('attendance_settings', 'half_day_threshold_minutes')) {
                $table->dropColumn('half_day_threshold_minutes');
            }
        });
    }
};
