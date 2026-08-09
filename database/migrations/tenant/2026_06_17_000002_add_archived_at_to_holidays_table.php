<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            if (! Schema::hasColumn('holidays', 'archived_at')) {
                $table->timestamp('archived_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            if (Schema::hasColumn('holidays', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
        });
    }
};
