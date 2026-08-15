<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password_changed_notice')) {
                $table->boolean('password_changed_notice')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'password_changed_by_role')) {
                $table->string('password_changed_by_role')->nullable()->after('password_changed_notice');
            }
            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('password_changed_by_role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_changed_notice')) {
                $table->dropColumn('password_changed_notice');
            }
            if (Schema::hasColumn('users', 'password_changed_by_role')) {
                $table->dropColumn('password_changed_by_role');
            }
            if (Schema::hasColumn('users', 'password_changed_at')) {
                $table->dropColumn('password_changed_at');
            }
        });
    }
};
