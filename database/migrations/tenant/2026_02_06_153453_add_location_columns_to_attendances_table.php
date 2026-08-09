<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationColumnsToAttendancesTable extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'clock_in_latitude')) {
                $table->decimal('clock_in_latitude', 10, 8)->nullable();
            }
            if (! Schema::hasColumn('attendances', 'clock_in_longitude')) {
                $table->decimal('clock_in_longitude', 11, 8)->nullable();
            }
            if (! Schema::hasColumn('attendances', 'clock_in_address')) {
                $table->string('clock_in_address')->nullable();
            }
            if (! Schema::hasColumn('attendances', 'clock_out_latitude')) {
                $table->decimal('clock_out_latitude', 10, 8)->nullable();
            }
            if (! Schema::hasColumn('attendances', 'clock_out_longitude')) {
                $table->decimal('clock_out_longitude', 11, 8)->nullable();
            }
            if (! Schema::hasColumn('attendances', 'clock_out_address')) {
                $table->string('clock_out_address')->nullable();
            }
            if (! Schema::hasColumn('attendances', 'work_from_type')) {
                $table->string('work_from_type')->default('office');
            } else {
                $table->string('work_from_type')->default('office')->change();
            }
            if (! Schema::hasColumn('attendances', 'total_hours')) {
                $table->decimal('total_hours', 5, 2)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $cols = array_filter([
                'clock_in_latitude',
                'clock_in_longitude',
                'clock_in_address',
                'clock_out_latitude',
                'clock_out_longitude',
                'clock_out_address',
                'total_hours'
            ], fn($c) => Schema::hasColumn('attendances', $c));

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
}
