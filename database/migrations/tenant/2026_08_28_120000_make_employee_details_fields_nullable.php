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
        try {
            DB::statement("ALTER TABLE `employee_details` MODIFY `parent_dpt_id` BIGINT UNSIGNED NULL;");
        } catch (\Throwable $e) {}

        try {
            DB::statement("ALTER TABLE `employee_details` MODIFY `employee_id` VARCHAR(191) NULL;");
        } catch (\Throwable $e) {}

        try {
            DB::statement("ALTER TABLE `employee_details` MODIFY `joining_date` DATE NULL;");
        } catch (\Throwable $e) {}

        try {
            DB::statement("ALTER TABLE `employee_details` MODIFY `business_address` TEXT NULL;");
        } catch (\Throwable $e) {}

        try {
            DB::statement("ALTER TABLE `employee_details` MODIFY `status` VARCHAR(191) NULL DEFAULT 'active';");
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
