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
        if (Schema::hasTable('designations')) {
            Schema::table('designations', function (Blueprint $table) {
                if (! Schema::hasColumn('designations', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
                if (! Schema::hasColumn('designations', 'parent_id')) {
                    $table->unsignedBigInteger('parent_id')->nullable()->after('name')->index();
                }
                if (! Schema::hasColumn('designations', 'unique_code')) {
                    $table->string('unique_code')->nullable()->after('name');
                }
                if (! Schema::hasColumn('designations', 'level')) {
                    $table->integer('level')->default(0)->after('name');
                }
                if (! Schema::hasColumn('designations', 'order')) {
                    $table->integer('order')->default(0)->after('name');
                }
                if (! Schema::hasColumn('designations', 'status')) {
                    $table->string('status', 50)->default('active')->after('name');
                }
                if (! Schema::hasColumn('designations', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('updated_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('designations')) {
            Schema::table('designations', function (Blueprint $table) {
                if (Schema::hasColumn('designations', 'parent_id')) {
                    $table->dropColumn('parent_id');
                }
                if (Schema::hasColumn('designations', 'unique_code')) {
                    $table->dropColumn('unique_code');
                }
                if (Schema::hasColumn('designations', 'order')) {
                    $table->dropColumn('order');
                }
            });
        }
    }
};
