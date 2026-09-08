<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'deadline')) {
                $table->date('deadline')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('tickets', 'affected_module')) {
                $table->string('affected_module')->nullable()->after('project_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['deadline', 'affected_module']);
        });
    }
};
