<?php

namespace App\Services;

use Illuminate\Support\Facades\{DB, Hash, Schema};

class RevokedCompanyCredentials
{
    private ?string $schemaDatabase = null;

    public function ensureSchema(): void
    {
        $database = DB::connection('central')->getDatabaseName();
        if ($this->schemaDatabase === $database) return;
        if (!Schema::connection('central')->hasTable('revoked_company_credentials')) {
            (require database_path('migrations/central/2026_10_09_170000_create_revoked_company_credentials.php'))->up();
        }
        $this->schemaDatabase = $database;
    }

    public function remember(int $companyId, string $login, string $passwordHash): void
    {
        if (!$login || !$passwordHash) return;
        $this->ensureSchema();
        foreach (DB::connection('central')->table('revoked_company_credentials')
            ->where('company_id', $companyId)->where('login_fingerprint', $this->fingerprint($login))->cursor() as $existing) {
            if ($existing->password_hash === $passwordHash
                || (!password_get_info($passwordHash)['algo'] && SecureCompanyLogin::passwordMatches($passwordHash, $existing->password_hash))) return;
        }
        $hash = password_get_info($passwordHash)['algo'] ? $passwordHash : Hash::make($passwordHash);
        DB::connection('central')->table('revoked_company_credentials')->insert([
            'company_id' => $companyId, 'login_fingerprint' => $this->fingerprint($login),
            'password_hash' => $hash, 'created_at' => now(),
        ]);
    }

    public function rejects(string $login, string $password): bool
    {
        $this->ensureSchema();
        foreach (DB::connection('central')->table('revoked_company_credentials')
            ->where('login_fingerprint', $this->fingerprint($login))->cursor() as $credential) {
            if (SecureCompanyLogin::passwordMatches($password, $credential->password_hash)) return true;
        }
        return false;
    }

    private function fingerprint(string $login): string
    {
        return hash_hmac('sha256', strtolower(trim($login)), (string) config('app.key'));
    }
}
