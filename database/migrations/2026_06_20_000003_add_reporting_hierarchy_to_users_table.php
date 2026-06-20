<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'reports_to_id')) {
                $table->foreignId('reports_to_id')->nullable()->after('role')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->after('reports_to_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'hr_id')) {
                $table->foreignId('hr_id')->nullable()->after('manager_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['hr_id', 'manager_id', 'reports_to_id'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });
    }
};
