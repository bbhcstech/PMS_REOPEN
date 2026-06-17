<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (! Schema::hasColumn('leaves', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('leave_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (Schema::hasColumn('leaves', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
        });
    }
};
