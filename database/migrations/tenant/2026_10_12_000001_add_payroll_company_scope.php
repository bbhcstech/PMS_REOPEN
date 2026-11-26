<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['salary_structures', 'payrolls', 'payroll_histories'] as $name) {
            if (Schema::hasTable($name) && ! Schema::hasColumn($name, 'company_id')) {
                Schema::table($name, fn (Blueprint $table) => $table->unsignedBigInteger('company_id')->nullable()->index());
            }
        }
        if (! Schema::hasColumn('users', 'company_id')) return;

        // Preserve existing records; infer ownership only from the tenant's own users.
        $companyIds = DB::table('users')->where('company_id', '>', 0)->distinct()->pluck('company_id');
        foreach (['salary_structures' => 'created_by', 'payrolls' => 'generated_by', 'payroll_histories' => 'user_id'] as $name => $actorColumn) {
            if (! Schema::hasTable($name)) continue;
            if (Schema::hasColumn($name, $actorColumn)) {
                DB::table($name)->whereNull('company_id')->orderBy('id')->chunkById(500, function ($rows) use ($name, $actorColumn) {
                    foreach ($rows as $row) {
                        $companyId = DB::table('users')->where('id', $row->{$actorColumn})->value('company_id');
                        if ($companyId) DB::table($name)->where('id', $row->id)->whereNull('company_id')->update(['company_id' => $companyId]);
                    }
                });
            }
            if ($companyIds->count() === 1) {
                DB::table($name)->whereNull('company_id')->update(['company_id' => $companyIds->first()]);
            }
        }
    }

    public function down(): void
    {
        // Retain company ownership metadata and existing payroll records.
    }
};
