<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'employee_welcome_seen_at')) {
                $table->timestamp('employee_welcome_seen_at')->nullable()->after('remember_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'employee_welcome_seen_at')) {
                $table->dropColumn('employee_welcome_seen_at');
            }
        });
    }
};
