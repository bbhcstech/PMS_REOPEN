<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'designation')) {
                $table->string('designation')->nullable();
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable();
            }
            if (! Schema::hasColumn('users', 'dob')) {
                $table->date('dob')->nullable();
            }
            if (! Schema::hasColumn('users', 'marital_status')) {
                $table->enum('marital_status', ['single', 'married'])->nullable();
            }
            if (! Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable();
            }
            if (! Schema::hasColumn('users', 'about')) {
                $table->text('about')->nullable();
            }
            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country')->nullable();
            }
            if (! Schema::hasColumn('users', 'language')) {
                $table->string('language')->nullable();
            }
            if (! Schema::hasColumn('users', 'slack_id')) {
                $table->string('slack_id')->nullable();
            }
            if (! Schema::hasColumn('users', 'email_notify')) {
                $table->boolean('email_notify')->default(1);
            }
            if (! Schema::hasColumn('users', 'google_calendar')) {
                $table->boolean('google_calendar')->default(0);
            }
            if (! Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter([
                'designation', 'gender', 'dob', 'marital_status', 'address',
                'about', 'country', 'language', 'slack_id',
                'email_notify', 'google_calendar', 'profile_image'
            ], fn($c) => Schema::hasColumn('users', $c));

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
