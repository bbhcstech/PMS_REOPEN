<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parent_departments', function (Blueprint $table) {
            if (! Schema::hasColumn('parent_departments', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('parent_departments', function (Blueprint $table) {
            if (Schema::hasColumn('parent_departments', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
        });
    }
};
