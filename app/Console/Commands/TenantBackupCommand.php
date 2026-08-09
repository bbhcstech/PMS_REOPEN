<?php

namespace App\Console\Commands;

use App\Models\Central\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TenantBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:backup 
                            {--company= : Specific company code or db_name to backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup tenant MySQL database(s) to storage/app/backups directory.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $target = $this->option('company');

        $query = Company::on('central');

        if ($target) {
            $query->where(function ($q) use ($target) {
                $q->where('company_code', strtoupper($target))
                    ->orWhere('db_name', $target)
                    ->orWhere('id', $target);
            });
        }

        $companies = $query->get();

        if ($companies->isEmpty()) {
            $this->warn('No tenant companies found matching criteria.');
            return Command::SUCCESS;
        }

        $backupDir = storage_path('app/backups');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $host = config('database.connections.tenant.host', '127.0.0.1');
        $port = config('database.connections.tenant.port', '3306');
        $user = config('database.connections.tenant.username', 'root');
        $password = config('database.connections.tenant.password', '');

        $this->info("Starting backup for {$companies->count()} tenant database(s)...");
        $timestamp = now()->format('Y-m-d_H-i-s');

        $successCount = 0;

        foreach ($companies as $company) {
            $dbName = $company->db_name;
            $backupFile = "{$backupDir}/backup_{$dbName}_{$timestamp}.sql";

            $this->info("Backing up DB `{$dbName}` to {$backupFile}...");

            // Attempt mysqldump via command line
            $mysqldumpPath = env('MYSQLDUMP_PATH', 'mysqldump');
            $passArg = $password !== '' ? "-p\"{$password}\"" : '';

            $cmd = "{$mysqldumpPath} --host={$host} --port={$port} --user={$user} {$passArg} {$dbName} > \"{$backupFile}\"";

            exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && File::exists($backupFile) && File::size($backupFile) > 0) {
                $this->info("✔ Backup created successfully for `{$dbName}` (" . number_format(File::size($backupFile) / 1024, 2) . " KB)");
                $successCount++;
            } else {
                // Fallback to PHP native table schema export if mysqldump is not in system PATH
                $this->warn("mysqldump CLI execution returned code {$returnCode}. Running native PHP schema & data dumper...");
                
                try {
                    $this->exportDatabaseNative($dbName, $backupFile);
                    $this->info("✔ Backup created via native dumper for `{$dbName}` (" . number_format(File::size($backupFile) / 1024, 2) . " KB)");
                    $successCount++;
                } catch (\Throwable $e) {
                    $this->error("✖ Backup failed for `{$dbName}`: " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info("==================================================");
        $this->info("Tenant Backup Summary: {$successCount}/{$companies->count()} Backups Created.");
        $this->info("Backup Directory: {$backupDir}");
        $this->info("==================================================");

        return Command::SUCCESS;
    }

    /**
     * Native PHP Database Exporter fallback when mysqldump binary is not available.
     */
    private function exportDatabaseNative(string $dbName, string $filePath): void
    {
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');

        $tables = DB::connection('tenant')->select('SHOW TABLES');
        $tablesKey = "Tables_in_{$dbName}";

        $handle = fopen($filePath, 'w');
        fwrite($handle, "-- Multi-Tenant Backup for `{$dbName}`\n");
        fwrite($handle, "-- Date: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$tablesKey;

            // Dump table structure
            $createStmt = DB::connection('tenant')->select("SHOW CREATE TABLE `{$tableName}`");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            fwrite($handle, $createStmt[0]->{'Create Table'} . ";\n\n");

            // Dump table data
            $rows = DB::connection('tenant')->table($tableName)->get();
            foreach ($rows as $row) {
                $values = array_map(function ($val) {
                    if ($val === null) return 'NULL';
                    return DB::connection('tenant')->getPdo()->quote($val);
                }, (array) $row);

                fwrite($handle, "INSERT INTO `{$tableName}` VALUES (" . implode(',', $values) . ");\n");
            }
            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }
}
