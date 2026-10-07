<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (! Schema::hasColumn('attendances', 'location')) {
                    $table->string('location', 255)->nullable()->after('status');
                }
                if (! Schema::hasColumn('attendances', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable()->after('location');
                }
                if (! Schema::hasColumn('attendances', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                }
                if (! Schema::hasColumn('attendances', 'working_from')) {
                    $table->string('working_from', 100)->nullable()->after('clock_out');
                }
                if (! Schema::hasColumn('attendances', 'location_id')) {
                    $table->unsignedBigInteger('location_id')->nullable()->after('working_from');
                }
                if (! Schema::hasColumn('attendances', 'department_id')) {
                    $table->unsignedBigInteger('department_id')->nullable()->after('location_id');
                }
                if (! Schema::hasColumn('attendances', 'late')) {
                    $table->string('late', 10)->default('no')->after('department_id');
                }
                if (! Schema::hasColumn('attendances', 'half_day')) {
                    $table->string('half_day', 10)->default('no')->after('late');
                }
                if (! Schema::hasColumn('attendances', 'half_day_type')) {
                    $table->string('half_day_type', 50)->nullable()->after('half_day');
                }
                if (! Schema::hasColumn('attendances', 'overwrite_attendance')) {
                    $table->string('overwrite_attendance', 10)->nullable()->after('half_day_type');
                }
            });
        }

        // Clean up any corrupted skills in employee_details that contain SQL error logs
        if (Schema::hasTable('employee_details')) {
            try {
                DB::table('employee_details')
                    ->where(function ($query) {
                        $query->where('skills', 'LIKE', '%SQLSTATE%')
                            ->orWhere('skills', 'LIKE', '%Unknown column%')
                            ->orWhere('skills', 'LIKE', '%INSERT INTO%');
                    })
                    ->update(['skills' => null]);
            } catch (\Throwable $e) {
                // Ignore if query fails
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                $columns = [
                    'location',
                    'latitude',
                    'longitude',
                    'working_from',
                    'location_id',
                    'department_id',
                    'late',
                    'half_day',
                    'half_day_type',
                    'overwrite_attendance'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('attendances', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
