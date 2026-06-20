<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_details', 'directory_about')) {
                $table->text('directory_about')->nullable()->after('about');
            }

            if (! Schema::hasColumn('employee_details', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('skills');
            }

            if (! Schema::hasColumn('employee_details', 'portfolio_url')) {
                $table->string('portfolio_url')->nullable()->after('linkedin_url');
            }

            if (! Schema::hasColumn('employee_details', 'facebook_url')) {
                $table->string('facebook_url')->nullable()->after('portfolio_url');
            }

            if (! Schema::hasColumn('employee_details', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('facebook_url');
            }

            if (! Schema::hasColumn('employee_details', 'x_url')) {
                $table->string('x_url')->nullable()->after('instagram_url');
            }

            if (! Schema::hasColumn('employee_details', 'cv_path')) {
                $table->string('cv_path')->nullable()->after('x_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            foreach (['directory_about', 'linkedin_url', 'portfolio_url', 'facebook_url', 'instagram_url', 'x_url', 'cv_path'] as $column) {
                if (Schema::hasColumn('employee_details', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
