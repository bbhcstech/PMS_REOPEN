<?php

namespace App\Console\Commands;

use App\Models\Central\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:create {name : Company Name, e.g. "Acme Corp"} {slug : Short Slug identifier, e.g. "acme"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant database, register company in central DB, run migrations, and seed default admin.';

    /**
     * Get tenant database prefix.
     */
    private function getTenantDbPrefix(): string
    {
        $prefix = env('TENANT_DB_PREFIX');
        if ($prefix !== null && $prefix !== '') {
            return $prefix;
        }

        $dbName = (string) (config('database.connections.central.database') ?: config('database.connections.mysql.database', ''));
        if (str_contains($dbName, '_')) {
            $parts = explode('_', $dbName);
            return $parts[0] . '_';
        }

        return 'pms_';
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = trim($this->argument('name'));
        $rawSlug = strtolower(trim($this->argument('slug')));
        $slug = preg_replace('/[^a-z0-9_]/', '', $rawSlug);
        
        $dbPrefix = $this->getTenantDbPrefix();
        if ($dbPrefix && str_starts_with($slug, $dbPrefix)) {
            $dbName = $slug;
        } else {
            $dbName = $dbPrefix . $slug;
        }

        $this->info("Creating tenant company: {$name} (Database: {$dbName})...");

        // 1. Check if database name already exists in central registry
        $existing = Company::on('central')->where('db_name', $dbName)->orWhere('company_code', strtoupper($slug))->first();
        if ($existing) {
            $this->error("Company with database name '{$dbName}' or code '" . strtoupper($slug) . "' already exists!");
            return Command::FAILURE;
        }

        // 2. Create or verify physical MySQL database matching charset/collation utf8mb4_general_ci
        $dbVerified = false;
        try {
            $pdo = DB::connection('central')->getPdo();
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $this->info("✔ Created MySQL Database: `{$dbName}`");
            $dbVerified = true;
        } catch (\Throwable $e) {
            try {
                $tenantHost = config('database.connections.tenant.host') ?: config('database.connections.mysql.host', '127.0.0.1');
                $tenantPort = config('database.connections.tenant.port') ?: config('database.connections.mysql.port', 3306);
                $tenantUser = config('database.connections.tenant.username') ?: config('database.connections.mysql.username', 'root');
                $tenantPass = config('database.connections.tenant.password') ?: config('database.connections.mysql.password', '');

                new \PDO("mysql:host={$tenantHost};port={$tenantPort};dbname={$dbName}", $tenantUser, $tenantPass, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ]);
                $this->info("✔ Verified existing MySQL Database: `{$dbName}`");
                $dbVerified = true;
            } catch (\Throwable $ex) {
                $dbVerified = false;
            }

            if (! $dbVerified) {
                $this->error("Failed to create database '{$dbName}': " . $e->getMessage());
                return Command::FAILURE;
            }
        }

        // 3. Register company in pms_central.companies
        $company = Company::on('central')->create([
            'company_code'   => strtoupper($slug),
            'name'           => $name,
            'email'          => "admin@{$slug}.local",
            'password'       => 'password123',
            'db_name'        => $dbName,
            'status'         => 'active',
            'max_users'      => 100,
            'max_projects'   => 50,
            'max_clients'    => 100,
            'max_storage_mb' => 10000,
        ]);

        $this->info("✔ Registered Central Company ID: {$company->id}");

        // 4. Configure tenant connection dynamically and run tenant migrations
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');

        $this->info("Running tenant migrations for '{$dbName}'...");
        $exitCode = Artisan::call('migrate', [
            '--path'     => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force'    => true,
        ]);

        if ($exitCode !== 0) {
            $this->error("Migration failed:\n" . Artisan::output());
            return Command::FAILURE;
        }

        $this->info("✔ Migrations completed successfully.");

        // Ensure company record exists in tenant DB
        User::syncCompanyToConnection('tenant', $company);

        // 5. Seed default company admin user into the new tenant DB
        $adminEmail = "admin@{$slug}.com";
        $adminPassword = 'password123';

        $admin = User::on('tenant')->create([
            'name'          => $name . ' Admin',
            'email'         => $adminEmail,
            'password'      => Hash::make($adminPassword),
            'company_id'    => $company->id,
            'role'          => 'admin',
            'is_active'     => true,
            'login_allowed' => true,
        ]);

        $this->newLine();
        $this->info("=================================================");
        $this->info("🎉 Tenant Company Created Successfully!");
        $this->info("=================================================");
        $this->line(" Company Name : {$name}");
        $this->line(" Database Name: {$dbName}");
        $this->line(" Central ID   : {$company->id}");
        $this->line(" Admin Email  : {$adminEmail}");
        $this->line(" Password     : {$adminPassword}");
        $this->info("=================================================");

        return Command::SUCCESS;
    }
}
