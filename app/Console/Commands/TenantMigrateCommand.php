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
        $originalTenant = config('database.connections.tenant');
        $originalDefault = DB::getDefaultConnection();

        foreach ($companies as $company) {
            $this->info("--------------------------------------------------");
            $this->info(" Migrating Tenant: {$company->name} [DB: {$company->db_name}]");
            $this->info("--------------------------------------------------");

            try {
                $targetDb = trim((string) $company->db_name);
                if ($targetDb === '') throw new \RuntimeException('This company has no registered database.');
                // A denied company connection must fail, never migrate another company's fallback DB.
                config(['database.connections.tenant' => array_replace($originalTenant, ['database' => $targetDb, 'url' => null])]);
                DB::purge('tenant');
                DB::setDefaultConnection('tenant');
                DB::connection('tenant')->getPdo();

                $params = [
                    '--path'     => 'database/migrations/tenant',
                    '--database' => 'tenant',
                    '--force'    => true,
                ];

                $exitCode = Artisan::call('migrate', $params);

                if ($exitCode === 0) {
                    $this->info("✔ Migrations completed successfully for {$targetDb}.");

                    // Ensure Company record is synced into tenant DB companies table
                    try {
                        \App\Models\User::syncCompanyToConnection('tenant', $company, strict: true);
                    } catch (\Throwable $syncEx) {
                        throw new \RuntimeException('Migrations completed but company registry synchronization failed.', 0, $syncEx);
                    }

                    $this->line(trim(Artisan::output()));

                    if ($this->option('seed')) {
                        $this->info("Running tenant seeders...");
                        if (Artisan::call('db:seed', ['--database' => 'tenant', '--force' => true]) !== 0) {
                            throw new \RuntimeException('Tenant seeding failed: '.Artisan::output());
                        }
                    }

                    $successCount++;
                } else {
                    $this->error("✖ Migration failed for {$targetDb}:");
                    $this->error(Artisan::output());
                    $failureCount++;
                }
            } catch (\Throwable $e) {
                $this->error("✖ Error connecting or migrating database '{$company->db_name}': " . $e->getMessage());
                $failureCount++;
            } finally {
                DB::purge('tenant');
                config(['database.connections.tenant' => $originalTenant]);
                DB::setDefaultConnection($originalDefault);
            }

            $this->newLine();
        }

        $this->info("==================================================");
        $this->info("Tenant Migration Summary: {$successCount} Succeeded, {$failureCount} Failed.");
        $this->info("==================================================");

        return $failureCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
