<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            if (! Schema::hasColumn('designations', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('designations', function (Blueprint $table) {
            if (Schema::hasColumn('designations', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
        });
    }
};
