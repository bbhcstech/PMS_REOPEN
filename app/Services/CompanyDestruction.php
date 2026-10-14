<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\{DB, Schema};

class CompanyDestruction
{
    public function destroy(Company $company): void
    {
        abort_unless(TenantScope::isPlatformAdmin(), 403);
        $database = $company->db_name;
        // Never drop a shared/platform/session database or one reused by another company.
        $protected = array_filter([DB::connection('central')->getDatabaseName(), DB::connection('session_db')->getDatabaseName()]);
        abort_unless($database && preg_match('/\A[a-zA-Z0-9_\-]+\z/', $database)
            && !in_array($database, $protected, true)
            && !Company::withTrashed()->where('db_name', $database)->where('id', '!=', $company->id)->exists(), 409, 'This database cannot safely be destroyed.');
        $revocations = app(RevokedCompanyCredentials::class);
        $revocations->ensureSchema();
        $original = config('database.connections.tenant');
        try {
            config(['database.connections.tenant.database' => $database]); DB::purge('tenant');
            DB::connection('central')->transaction(function () use ($company, $database, $revocations) {
                $locked = Company::whereKey($company->id)->lockForUpdate()->firstOrFail();
                abort_unless($locked->db_name === $database, 409);
                $ongoing = DB::connection('central')->table('company_subscriptions')
                    ->where('company_id', $locked->id)->whereIn('status', ['active', 'trial', 'suspended'])
                    ->where('ends_at', '>=', now())->exists();
                $ongoing = $ongoing || ($locked->trial_ends_at && \Illuminate\Support\Carbon::parse($locked->trial_ends_at)->isFuture());
                abort_if($ongoing && !$locked->manually_suspended, 409,
                    'This company has an ongoing subscription. Suspend it with a reason first, then permanently delete it from Suspended Companies.');
                if (Schema::connection('tenant')->hasTable('users')) {
                    foreach (DB::connection('tenant')->table('users')->where('company_id', $locked->id)->cursor() as $user) {
                        $revocations->remember($locked->id, (string) $user->email, (string) $user->password);
                        if (!empty($user->personal_email)) $revocations->remember($locked->id, $user->personal_email, (string) $user->password);
                    }
                }
                foreach (array_filter([$locked->email, $locked->company_code, $locked->domain, $locked->subdomain]) as $alias) {
                    $revocations->remember($locked->id, $alias, (string) $locked->password);
                }
                // Keep a deletion tombstone in the registry so all existing sessions
                // are refused before any guard touches the database being dropped.
                $locked->password = null;
                $locked->save();
                $locked->delete();
            });
            $this->dropDatabase($database);
        } finally {
            config(['database.connections.tenant' => $original]); DB::purge('tenant');
        }
    }

    protected function dropDatabase(string $database): void
    {
        abort_unless(in_array(DB::connection('tenant')->getDriverName(), ['mysql', 'mariadb'], true), 409, 'Database destruction requires the configured MySQL server.');
        // Database was validated above; quote it as an identifier, never as SQL text.
        abort_unless(DB::connection('tenant')->statement('DROP DATABASE IF EXISTS `' . $database . '`'), 503, 'Company access has been revoked, but database cleanup failed.');
    }
}
