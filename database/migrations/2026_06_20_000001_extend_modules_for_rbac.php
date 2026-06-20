<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            if (! Schema::hasColumn('modules', 'route_name')) {
                $table->string('route_name')->nullable()->after('route_prefix');
            }
            if (! Schema::hasColumn('modules', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('route_name')->constrained('modules')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }
            if (Schema::hasColumn('modules', 'route_name')) {
                $table->dropColumn('route_name');
            }
        });
    }
};
