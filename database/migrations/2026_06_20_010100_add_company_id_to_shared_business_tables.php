<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'employee_details',
        'attendances',
        'leaves',
        'leave_requests',
        'projects',
        'tasks',
        'timelogs',
        'time_logs',
        'daily_timesheets',
        'payrolls',
        'payslips',
        'notifications',
        'salary_structures',
        'reports',
        'employee_hierarchy',
        'departments',
        'parent_departments',
        'designations',
        'tickets',
        'clients',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'company_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'company_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                try {
                    $table->dropConstrainedForeignId('company_id');
                } catch (Throwable $e) {
                    if (Schema::hasColumn($tableName, 'company_id')) {
                        $table->dropColumn('company_id');
                    }
                }
            });
        }
    }
};
