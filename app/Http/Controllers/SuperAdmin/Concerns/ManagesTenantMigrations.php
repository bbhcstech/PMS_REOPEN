<?php

namespace App\Http\Controllers\SuperAdmin\Concerns;

use App\Models\Central\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

trait ManagesTenantMigrations
{
    /**
     * Display Tenant Migration Control Center.
     */
    /**
     * Persist one tenant migration run; the Migrations page reads its times from these rows.
     */
    private function recordMigrationRun($company, bool $success, string $duration, int $appliedCount, string $output = ''): void
    {
        try {
            \App\Models\Central\SuperAdminActivityLog::record(
                'tenant.migration_executed',
                (int) $company->id,
                ($success ? 'Migration executed for ' : 'Migration failed for ') . $company->name,
                [
                    'company_name'   => $company->name,
                    'db_name'        => $company->db_name,
                    'version'        => 'v' . $appliedCount,
                    'execution_time' => $duration,
                    'status'         => $success ? 'success' : 'failed',
                    'output'         => Str::limit($output, 4000),
                ],
                auth('super_admin')->id()
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Latest recorded migration run per company id (newest first).
     */
    private function latestMigrationRuns(): \Illuminate\Support\Collection
    {
        try {
            return \App\Models\Central\SuperAdminActivityLog::where('action', 'tenant.migration_executed')
                ->whereNotNull('company_id')
                ->latest('created_at')->latest('id')
                ->get(['id', 'company_id', 'meta', 'created_at'])
                ->unique('company_id')
                ->keyBy('company_id');
        } catch (\Throwable $e) {
            return collect();
        }
    }

    public function migrations(Request $request): View
    {
        try {
            $companies = Company::on('central')->latest()->get();
        } catch (\Throwable $e) {
            $companies = collect();
        }

        if ($companies->isEmpty()) {
            try {
                $companies = \App\Models\Company::latest()->get();
            } catch (\Throwable $e) {
                $companies = collect();
            }
        }

        // 1. Scan available tenant migration files
        $tenantMigrationPath = base_path('database/migrations/tenant');
        $allMigrationFiles = [];
        if (File::exists($tenantMigrationPath)) {
            $files = File::files($tenantMigrationPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $filename = $file->getFilenameWithoutExtension();
                    $allMigrationFiles[] = [
                        'file' => $file->getFilename(),
                        'name' => Str::after($filename, Str::substr($filename, 0, 18)),
                        'raw_name' => $filename,
                    ];
                }
            }
        }
        $totalAvailableMigrations = count($allMigrationFiles);

        // 2. Inspect dynamic tenant databases
        $tenantMigrationData = [];
        $upToDateCount = 0;
        $pendingCount = 0;
        $failedCount = 0;
        $latestRuns = $this->latestMigrationRuns();
        $lastRunAt = $latestRuns->max('created_at');

        foreach ($companies as $comp) {
            $status = 'unknown';
            $appliedList = [];
            $appliedCount = 0;
            $pendingList = $allMigrationFiles;
            $run = $latestRuns->get($comp->id);
            $dbConnected = false;

            try {
                config(['database.connections.tenant.database' => $comp->db_name]);
                DB::purge('tenant');
                DB::connection('tenant')->getPdo();
                $dbConnected = true;

                if (DB::connection('tenant')->getSchemaBuilder()->hasTable('migrations')) {
                    $appliedRecords = DB::connection('tenant')->table('migrations')->get();
                    $appliedList = $appliedRecords->pluck('migration')->toArray();
                    $appliedCount = count($appliedList);


                    $pendingList = array_values(array_filter($allMigrationFiles, function ($mig) use ($appliedList) {
                        return !in_array($mig['raw_name'], $appliedList, true);
                    }));

                    $pCount = count($pendingList);

                    if ($pCount === 0) {
                        $status = 'up_to_date';
                        $upToDateCount++;
                    } else {
                        $status = 'pending';
                        $pendingCount++;
                    }
                } else {
                    $status = 'not_initialized';
                    $pendingCount++;
                }
            } catch (\Throwable $e) {
                $status = 'failed';
                $failedCount++;
            }

            $currentVersionLabel = 'v' . $appliedCount;
            $latestVersionLabel = 'v' . $totalAvailableMigrations;

            $tenantMigrationData[] = [
                'company'                => $comp,
                'id'                     => $comp->id,
                'name'                   => $comp->name,
                'company_code'           => $comp->company_code ?? 'TEN-' . str_pad($comp->id, 3, '0', STR_PAD_LEFT),
                'db_name'                => $comp->db_name,
                'db_connected'           => $dbConnected,
                'current_version'        => $currentVersionLabel,
                'latest_version'         => $latestVersionLabel,
                'applied_count'          => $appliedCount,
                'total_count'            => $totalAvailableMigrations,
                'pending_count'          => count($pendingList),
                'pending_list'           => $pendingList,
                'status'                 => $status,
                // Real recorded run (created_at in app timezone); tenants never migrated from this panel show provisioning time.
                'last_migration'         => $run ? $run->created_at->format('d M Y, h:i A') : ($comp->created_at ? 'Provisioned ' . $comp->created_at->format('d M Y, h:i A') : 'Never'),
                'last_migration_iso'     => $run?->created_at?->toIso8601String(),
                'execution_time'         => $run && isset($run->meta['execution_time']) ? $run->meta['execution_time'] . 's' : '—',
            ];
        }

        $kpi = [
            'total_tenants'      => $companies->count(),
            'up_to_date'         => $upToDateCount,
            'pending_migrations' => $pendingCount,
            'failed_migrations'  => $failedCount,
            'last_run'           => $lastRunAt ? $lastRunAt->diffForHumans() : 'Never',
            'last_run_iso'       => $lastRunAt?->toIso8601String(),
            'last_run_at'        => $lastRunAt?->format('d M Y, h:i A'),
        ];

        // 3. Fetch migration activity logs
        $migrationHistory = [];
        try {
            $logs = \App\Models\Central\SuperAdminActivityLog::with(['superAdmin', 'company'])
                ->where('action', 'like', '%migration%')
                ->latest()
                ->take(15)
                ->get();
            foreach ($logs as $log) {
                $migrationHistory[] = [
                    'company_name'   => $log->meta['company_name'] ?? $log->company?->name ?? 'Tenant',
                    'migration'      => $log->meta['migration'] ?? 'tenant:migrate',
                    'version'        => $log->meta['version'] ?? '—',
                    'status'         => $log->meta['status'] ?? 'success',
                    'execution_time' => isset($log->meta['execution_time']) ? $log->meta['execution_time'] . 's' : '—',
                    'executed_at'    => $log->created_at->format('d M Y, h:i A'),
                    'executed_by'    => $log->superAdmin?->name ?? 'Super Admin',
                ];
            }
        } catch (\Throwable $e) {}

        $currentCompanyDb = session('current_company_db');

        return view('superadmin.migrations.index', compact(
            'companies',
            'tenantMigrationData',
            'kpi',
            'allMigrationFiles',
            'migrationHistory',
            'currentCompanyDb'
        ));
    }

    /**
     * Run migration for a single tenant database.
     */
    public function runMigration(Request $request): \Illuminate\Http\JsonResponse
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $request->validate([
            'company_id' => 'required',
        ]);

        $companyId = $request->input('company_id');

        try {
            $company = Company::on('central')->find($companyId) ?? \App\Models\Company::find($companyId);
            if (!$company) {
                return response()->json(['success' => false, 'message' => 'Tenant company not found.'], 404);
            }

            $startTime = microtime(true);

            // Configure connection dynamically
            config(['database.connections.tenant.database' => $company->db_name]);
            DB::purge('tenant');
            DB::connection('tenant')->getPdo();

            $params = [
                '--path'     => 'database/migrations/tenant',
                '--database' => 'tenant',
                '--force'    => true,
            ];

            $exitCode = Artisan::call('migrate', $params);
            $output = trim(Artisan::output());
            $duration = number_format(microtime(true) - $startTime, 2);

            // Calculate updated applied count
            $appliedCount = 0;
            try {
                $appliedCount = DB::connection('tenant')->table('migrations')->count();
            } catch (\Throwable $e) {}

            $tenantMigrationPath = base_path('database/migrations/tenant');
            $totalAvailable = 0;
            if (File::exists($tenantMigrationPath)) {
                $totalAvailable = count(File::files($tenantMigrationPath));
            }

            $this->recordMigrationRun($company, $exitCode === 0, $duration, $appliedCount, $output);

            if ($exitCode === 0) {
                return response()->json([
                    'success'        => true,
                    'company_id'     => $company->id,
                    'company_name'   => $company->name,
                    'db_name'        => $company->db_name,
                    'current_version'=> 'v' . $appliedCount,
                    'latest_version' => 'v' . $totalAvailable,
                    'pending_count'  => max(0, $totalAvailable - $appliedCount),
                    'execution_time' => $duration . 's',
                    'output'         => $output ?: "✔ Migrations completed successfully for {$company->db_name}.",
                    'message'        => "Migrations completed successfully for {$company->name} in {$duration}s.",
                ]);
            }

            return response()->json([
                'success'      => false,
                'company_id'   => $company->id,
                'company_name' => $company->name,
                'db_name'      => $company->db_name,
                'output'       => $output ?: "✖ Migration failed.",
                'message'      => "Migration execution failed for {$company->name}.",
            ], 500);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Error migrating '{$companyId}': " . $e->getMessage(),
                'output'  => $e->getMessage() . "\n" . $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Run migration in bulk across multiple tenant databases.
     */
    public function bulkRunMigration(Request $request): \Illuminate\Http\JsonResponse
    {
        @set_time_limit(600);
        @ini_set('max_execution_time', '600');

        $request->validate([
            'company_ids'   => 'required|array',
            'company_ids.*' => 'required',
        ]);

        $companyIds = $request->input('company_ids');
        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($companyIds as $cId) {
            try {
                $comp = Company::on('central')->find($cId) ?? \App\Models\Company::find($cId);
                if (!$comp) continue;

                $startTime = microtime(true);
                config(['database.connections.tenant.database' => $comp->db_name]);
                DB::purge('tenant');

                $exitCode = Artisan::call('migrate', [
                    '--path'     => 'database/migrations/tenant',
                    '--database' => 'tenant',
                    '--force'    => true,
                ]);

                $output = trim(Artisan::output());
                $duration = number_format(microtime(true) - $startTime, 2);
                $appliedCount = 0;
                try {
                    $appliedCount = DB::connection('tenant')->table('migrations')->count();
                } catch (\Throwable $e) {}
                $this->recordMigrationRun($comp, $exitCode === 0, $duration, $appliedCount, $output);

                if ($exitCode === 0) {
                    $successCount++;
                    $results[] = [
                        'company_id'   => $comp->id,
                        'name'         => $comp->name,
                        'db_name'      => $comp->db_name,
                        'status'       => 'success',
                        'time'         => $duration . 's',
                        'output'       => $output,
                    ];
                } else {
                    $failedCount++;
                    $results[] = [
                        'company_id'   => $comp->id,
                        'name'         => $comp->name,
                        'db_name'      => $comp->db_name,
                        'status'       => 'failed',
                        'time'         => $duration . 's',
                        'output'       => $output,
                    ];
                }
            } catch (\Throwable $e) {
                $failedCount++;
                $results[] = [
                    'company_id'   => $cId,
                    'name'         => 'Company #' . $cId,
                    'status'       => 'failed',
                    'time'         => '0.00s',
                    'output'       => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success'       => true,
            'success_count' => $successCount,
            'failed_count'  => $failedCount,
            'total'         => count($companyIds),
            'results'       => $results,
            'message'       => "Bulk migration completed: {$successCount} Succeeded, {$failedCount} Failed.",
        ]);
    }

    /**
     * Retrieve migration logs for a specific tenant company.
     */
    public function migrationLogs(Request $request, $companyId): \Illuminate\Http\JsonResponse
    {
        try {
            $company = Company::on('central')->find($companyId) ?? \App\Models\Company::find($companyId);
            if (!$company) {
                return response()->json(['success' => false, 'message' => 'Tenant company not found.'], 404);
            }

            $logs = [];
            try {
                config(['database.connections.tenant.database' => $company->db_name]);
                DB::purge('tenant');

                if (DB::connection('tenant')->getSchemaBuilder()->hasTable('migrations')) {
                    // Recorded panel runs carry real times; the tenant migrations table itself has no timestamps.
                    $runs = \App\Models\Central\SuperAdminActivityLog::where('action', 'tenant.migration_executed')
                        ->where('company_id', $company->id)->latest('created_at')->latest('id')->take(20)->get();
                    foreach ($runs as $run) {
                        $failed = ($run->meta['status'] ?? 'success') !== 'success';
                        $logs[] = [
                            'timestamp' => $run->created_at->format('d M Y, h:i:s A'),
                            'message'   => ($failed ? 'Migration run failed' : 'Migration run completed')
                                . ' (' . ($run->meta['version'] ?? '—') . ', ' . ($run->meta['execution_time'] ?? '—') . 's)',
                            'status'    => $failed ? 'ERROR' : 'SUCCESS',
                        ];
                    }
                    $records = DB::connection('tenant')->table('migrations')->orderBy('id', 'desc')->get();
                    foreach ($records as $rec) {
                        $logs[] = [
                            'timestamp' => 'Batch #' . $rec->batch,
                            'message'   => "Applied migration: {$rec->migration}",
                            'status'    => 'SUCCESS',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                $logs[] = [
                    'timestamp' => now()->format('H:i:s'),
                    'message'   => 'Error connecting to database: ' . $e->getMessage(),
                    'status'    => 'ERROR',
                ];
            }

            return response()->json([
                'success'      => true,
                'company_name' => $company->name,
                'db_name'      => $company->db_name,
                'logs'         => $logs,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
