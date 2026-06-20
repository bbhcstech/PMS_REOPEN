<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('app_settings', 'description')) {
                $table->text('description')->nullable()->after('label');
            }

            if (! Schema::hasColumn('app_settings', 'min_value')) {
                $table->string('min_value')->nullable()->after('options');
            }

            if (! Schema::hasColumn('app_settings', 'max_value')) {
                $table->string('max_value')->nullable()->after('min_value');
            }

            if (! Schema::hasColumn('app_settings', 'unit')) {
                $table->string('unit')->nullable()->after('max_value');
            }

            if (! Schema::hasColumn('app_settings', 'section')) {
                $table->string('section')->nullable()->after('placeholder');
            }

            if (! Schema::hasColumn('app_settings', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('page');
            }
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            foreach (['sort_order', 'section', 'unit', 'max_value', 'min_value', 'description'] as $column) {
                if (Schema::hasColumn('app_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
