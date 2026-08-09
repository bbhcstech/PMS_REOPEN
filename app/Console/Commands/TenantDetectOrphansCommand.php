<?php

namespace App\Console\Commands;

use App\Models\Central\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantDetectOrphansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:detect-orphans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect physical MySQL databases starting with pms_ that are not registered in central database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Scanning MySQL server for tenant databases...");

        // 1. Fetch physical databases matching pms_% pattern
        try {
            $databases = DB::connection('central')
                ->select("SELECT schema_name FROM information_schema.schemata WHERE schema_name LIKE 'pms_%'");
            
            $physicalDbs = array_map(fn ($row) => $row->schema_name, $databases);
        } catch (\Throwable $e) {
            $this->error("Failed to query information_schema: " . $e->getMessage());
            return Command::FAILURE;
        }

        // 2. Fetch registered db_names from pms_central.companies
        $registeredDbs = Company::on('central')->pluck('db_name')->toArray();
        
        // Always exclude central DB from orphan consideration
        $excludedDbs = ['pms_central'];

        $orphanedDbs = [];
        $registeredCount = 0;

        foreach ($physicalDbs as $db) {
            if (in_array($db, $excludedDbs, true)) {
                continue;
            }

            if (! in_array($db, $registeredDbs, true)) {
                $orphanedDbs[] = $db;
            } else {
                $registeredCount++;
            }
        }

        $this->newLine();
        $this->info("==================================================");
        $this->info(" Tenant Database Audit Report");
        $this->info("==================================================");
        $this->line(" Physical `pms_*` DBs Found : " . count($physicalDbs));
        $this->line(" Registered Central DBs     : {$registeredCount}");
        $this->line(" Orphaned / Untracked DBs   : " . count($orphanedDbs));
        $this->info("==================================================");

        if (! empty($orphanedDbs)) {
            $this->newLine();
            $this->warn("⚠ Detected Orphaned Databases (Physical DB exists but missing from central registry):");
            foreach ($orphanedDbs as $index => $orphanDb) {
                $this->line("  " . ($index + 1) . ". {$orphanDb}");
            }
            $this->newLine();
            $this->comment("Recommendation: Investigate these databases before manually registering or dropping them.");
        } else {
            $this->info("✔ All physical tenant databases match central registry. No orphans detected.");
        }

        return Command::SUCCESS;
    }
}
