<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CENTRAL migration.
 * Adds extended profile fields to the super_admins table so the
 * Super Admin Profile form can persist all submitted data.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->table('super_admins', function (Blueprint $table) {
            if (!Schema::connection('central')->hasColumn('super_admins', 'mobile')) {
                $table->string('mobile', 50)->nullable()->after('profile_image');
            }
            if (!Schema::connection('central')->hasColumn('super_admins', 'gender')) {
                $table->string('gender', 20)->nullable()->after('mobile');
            }
            if (!Schema::connection('central')->hasColumn('super_admins', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('gender');
            }
            if (!Schema::connection('central')->hasColumn('super_admins', 'marital_status')) {
                $table->string('marital_status', 50)->nullable()->after('date_of_birth');
            }
            if (!Schema::connection('central')->hasColumn('super_admins', 'country')) {
                $table->string('country', 100)->nullable()->after('marital_status');
            }
            if (!Schema::connection('central')->hasColumn('super_admins', 'language')) {
                $table->string('language', 100)->nullable()->after('country');
            }
            if (!Schema::connection('central')->hasColumn('super_admins', 'address')) {
                $table->text('address')->nullable()->after('language');
            }
            if (!Schema::connection('central')->hasColumn('super_admins', 'about')) {
                $table->text('about')->nullable()->after('address');
            }
            if (!Schema::connection('central')->hasColumn('super_admins', 'govt_id_card')) {
                $table->string('govt_id_card')->nullable()->after('about');
            }
            if (!Schema::connection('central')->hasColumn('super_admins', 'email_notifications')) {
                $table->boolean('email_notifications')->default(true)->after('govt_id_card');
            }
            if (!Schema::connection('central')->hasColumn('super_admins', 'google_calendar')) {
                $table->boolean('google_calendar')->default(false)->after('email_notifications');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('super_admins', function (Blueprint $table) {
            $cols = ['mobile', 'gender', 'date_of_birth', 'marital_status', 'country', 'language', 'address', 'about', 'govt_id_card', 'email_notifications', 'google_calendar'];
            foreach ($cols as $col) {
                if (Schema::connection('central')->hasColumn('super_admins', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
