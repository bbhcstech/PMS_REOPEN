<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CENTRAL migration — runs against pms_central only.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->table('modules', function (Blueprint $table) {
            if (! Schema::connection('central')->hasColumn('modules', 'route_name')) {
                $table->string('route_name')->nullable()->after('route_prefix');
            }
            if (! Schema::connection('central')->hasColumn('modules', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('route_name')->constrained('modules')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('modules', function (Blueprint $table) {
            if (Schema::connection('central')->hasColumn('modules', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }
            if (Schema::connection('central')->hasColumn('modules', 'route_name')) {
                $table->dropColumn('route_name');
            }
        });
    }
};
