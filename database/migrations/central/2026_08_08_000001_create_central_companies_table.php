<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CENTRAL migration — runs against pms_central only.
 * Command: php artisan migrate --path=database/migrations/central --database=central
 *
 * companies: the central registry of every tenant.
 * db_name: the actual MySQL database name holding this company's data.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_code')->nullable()->unique()->comment('Short identifier e.g. ACME');
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('domain')->unique()->nullable();
            $table->string('subdomain')->unique()->nullable();

            // THE key multi-tenancy column: MySQL DB name for this tenant.
            $table->string('db_name')->unique()->comment('Tenant MySQL database, e.g. pms_last');

            $table->text('address')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('employee_id_prefix')->nullable();
            $table->string('leave_prefix')->nullable();
            $table->string('payroll_prefix')->nullable();
            $table->string('payslip_prefix')->nullable();
            $table->string('greeting_message')->nullable();
            $table->json('theme')->nullable();
            $table->json('settings')->nullable();

            $table->enum('status', ['active', 'inactive', 'trial', 'suspended'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();

            $table->integer('max_users')->default(10);
            $table->integer('max_projects')->default(5);
            $table->integer('max_clients')->default(50);
            $table->bigInteger('max_storage_mb')->default(1024);

            $table->string('letterhead_file')->nullable();
            $table->string('letterhead_original_name')->nullable();
            $table->string('letterhead_file_type')->nullable();
            $table->timestamp('letterhead_uploaded_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('companies');
    }
};
