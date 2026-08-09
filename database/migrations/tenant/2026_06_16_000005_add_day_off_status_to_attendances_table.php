<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY status ENUM('present','absent','holiday','late','half_day','leave','day_off') DEFAULT 'absent'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY status ENUM('present','absent','holiday','late','half_day','leave') DEFAULT 'absent'");
    }
};
