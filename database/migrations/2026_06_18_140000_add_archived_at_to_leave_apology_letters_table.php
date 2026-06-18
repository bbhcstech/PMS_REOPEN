<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_apology_letters', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_apology_letters', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('reviewed_at')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_apology_letters', function (Blueprint $table) {
            if (Schema::hasColumn('leave_apology_letters', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
        });
    }
};
