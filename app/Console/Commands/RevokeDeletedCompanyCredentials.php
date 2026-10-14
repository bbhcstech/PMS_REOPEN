<?php

namespace App\Console\Commands;

use App\Models\Central\Company;
use App\Services\RevokedCompanyCredentials;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Schema};

class RevokeDeletedCompanyCredentials extends Command
{
    protected $signature = 'companies:revoke-deleted-credentials';
    protected $description = 'Revoke recoverable credentials left by previously deleted companies without modifying active company accounts.';

    public function handle(RevokedCompanyCredentials $revocations): int
    {
        $revocations->ensureSchema();
        $processed = 0; $unavailable = 0;
        foreach (Company::onlyTrashed()->cursor() as $company) {
            foreach (array_filter([$company->email, $company->company_code, $company->domain, $company->subdomain]) as $alias) {
                $revocations->remember($company->id, $alias, (string) $company->password);
            }
            foreach (['tenant', 'session_db'] as $source) {
                $config = config("database.connections.$source");
                if ($source === 'tenant') $config['database'] = $company->db_name;
                config(['database.connections.revocation_source' => $config]); DB::purge('revocation_source');
                try {
                    if (Schema::connection('revocation_source')->hasTable('users')) {
                        foreach (DB::connection('revocation_source')->table('users')->where('company_id', $company->id)->cursor() as $user) {
                            $revocations->remember($company->id, (string) $user->email, (string) $user->password);
                            if (!empty($user->personal_email)) $revocations->remember($company->id, $user->personal_email, (string) $user->password);
                        }
                    }
                } catch (\Throwable $e) { $unavailable++; }
                finally { DB::purge('revocation_source'); }
            }
            $processed++;
        }
        $this->info("Processed $processed deleted companies. Active company accounts were unchanged.");
        if ($unavailable) $this->warn("$unavailable sources were unavailable; only recoverable credential hashes could be revoked.");
        return self::SUCCESS;
    }
}
