<?php

namespace App\Console\Commands;

use App\Models\Central\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TenantMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate 
                            {--company= : Specific company code or db_name to migrate}
                            {--seed : Run tenant seeders after migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely run tenant database migrations across all registered tenant companies.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $target = $this->option('company');

        $query = Company::on('central')->where('status', '!=', 'inactive');

        if ($target) {
            $query->where(function ($q) use ($target) {
                $q->where('company_code', strtoupper($target))
                    ->orWhere('db_name', $target)
                    ->orWhere('id', $target);
            });
        }

        $companies = $query->get();

        if ($companies->isEmpty()) {
            $this->warn('No active tenant companies found matching criteria.');
            return Command::SUCCESS;
        }

        $this->info("Found {$companies->count()} tenant company database(s) to migrate.");
        $this->newLine();

        $successCount = 0;
        $failureCount = 0;

        foreach ($companies as $company) {
            $this->info("--------------------------------------------------");
            $this->info(" Migrating Tenant: {$company->name} [DB: {$company->db_name}]");
            $this->info("--------------------------------------------------");

            try {
                // Configure connection dynamically
                config(['database.connections.tenant.database' => $company->db_name]);
                DB::purge('tenant');

                // Test connection
                DB::connection('tenant')->getPdo();

                $params = [
                    '--path'     => 'database/migrations/tenant',
                    '--database' => 'tenant',
                    '--force'    => true,
                ];

                $exitCode = Artisan::call('migrate', $params);

                if ($exitCode === 0) {
                    $this->info("✔ Migrations completed successfully for {$company->db_name}.");

                    // Ensure Company record is synced into tenant DB companies table
                    \App\Models\User::syncCompanyToConnection('tenant', $company);

                    $this->line(trim(Artisan::output()));

                    if ($this->option('seed')) {
                        $this->info("Running tenant seeders...");
                        Artisan::call('db:seed', ['--database' => 'tenant', '--force' => true]);
                    }

                    $successCount++;
                } else {
                    $this->error("✖ Migration failed for {$company->db_name}:");
                    $this->error(Artisan::output());
                    $failureCount++;
                }
            } catch (\Throwable $e) {
                $this->error("✖ Error connecting or migrating database '{$company->db_name}': " . $e->getMessage());
                $failureCount++;
            }

            $this->newLine();
        }

        $this->info("==================================================");
        $this->info("Tenant Migration Summary: {$successCount} Succeeded, {$failureCount} Failed.");
        $this->info("==================================================");

        return $failureCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
