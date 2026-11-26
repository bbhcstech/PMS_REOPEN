<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connections = array_unique([config('database.default'), 'tenant']);

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('attendances')) {
                    DB::connection($conn)->statement("ALTER TABLE attendances MODIFY status ENUM('present','absent','holiday','late','half_day','leave','day_off','unpaid_leave','wfh') DEFAULT 'absent'");
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to modify attendances status on connection {$conn}: " . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        $connections = array_unique([config('database.default'), 'tenant']);

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('attendances')) {
                    DB::connection($conn)->statement("ALTER TABLE attendances MODIFY status ENUM('present','absent','holiday','late','half_day','leave','day_off','unpaid_leave') DEFAULT 'absent'");
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to rollback attendances status on connection {$conn}: " . $e->getMessage());
            }
        }
    }
};
