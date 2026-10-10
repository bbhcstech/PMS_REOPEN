<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\{Cache, DB, Schema};

/**
 * Real "last activity" of a company: the time of the latest request made by any user
 * signed in to that company's workspace, stored in central companies.last_activity_at.
 */
class CompanyActivity
{
    public const TIMEZONE = 'Asia/Kolkata';
    private const THROTTLE_SECONDS = 60;
    private static ?bool $ready = null;

    public static function ensureColumn(): bool
    {
        if (self::$ready !== null) return self::$ready;
        try {
            if (! Schema::connection('central')->hasColumn('companies', 'last_activity_at')) {
                (require database_path('migrations/central/2026_10_10_090000_add_last_activity_at_to_companies_table.php'))->up();
            }
            return self::$ready = true;
        } catch (\Throwable $e) {
            return self::$ready = false;
        }
    }

    /** Record activity for a company (written at most once a minute per company). */
    public static function touch(?int $companyId): void
    {
        if (! $companyId || ! self::ensureColumn()) return;
        try {
            if (! Cache::add('company-activity:' . $companyId, 1, self::THROTTLE_SECONDS)) return;
            DB::connection('central')->table('companies')->where('id', $companyId)
                ->update(['last_activity_at' => now()]);
        } catch (\Throwable $e) {}
    }

    /**
     * Last activity per company id. Companies with no recorded activity yet are filled once
     * from their existing real records (signed-in sessions and tenant audit logs).
     *
     * @return array<int, Carbon|null>
     */
    public static function forCompanies(iterable $companies): array
    {
        $result = [];
        if (! self::ensureColumn()) return $result;
        $companies = collect($companies);
        $stored = DB::connection('central')->table('companies')
            ->whereIn('id', $companies->pluck('id')->all())->pluck('last_activity_at', 'id');

        foreach ($companies as $company) {
            $value = $stored[$company->id] ?? null;
            if (! $value) {
                $value = self::backfill($company);
            }
            $result[$company->id] = $value ? Carbon::parse($value) : null;
        }
        return $result;
    }

    public static function format(?Carbon $time): string
    {
        return $time ? $time->copy()->timezone(self::TIMEZONE)->format('d M Y, h:i A') . ' IST' : 'No activity yet';
    }

    private static function backfill($company): ?string
    {
        $candidates = [];
        try {
            $sessions = DB::connection('session_db')->table('sessions')->whereNotNull('user_id')
                ->orderByDesc('last_activity')->limit(500)->get(['payload', 'last_activity']);
            foreach ($sessions as $session) {
                $payload = @unserialize(base64_decode((string) $session->payload), ['allowed_classes' => false]);
                if (is_array($payload) && (int) ($payload['current_company_id'] ?? 0) === (int) $company->id) {
                    $candidates[] = Carbon::createFromTimestamp((int) $session->last_activity)->setTimezone(config('app.timezone'));
                    break;
                }
            }
        } catch (\Throwable $e) {}

        if (! empty($company->db_name)) {
            try {
                $latest = DB::connection('central')->table(DB::raw('`' . str_replace('`', '', $company->db_name) . '`.`audit_logs`'))->max('created_at');
                if ($latest) $candidates[] = Carbon::parse($latest);
            } catch (\Throwable $e) {}
        }

        if (! $candidates) return null;
        $latest = collect($candidates)->max();
        try {
            DB::connection('central')->table('companies')->where('id', $company->id)->whereNull('last_activity_at')
                ->update(['last_activity_at' => $latest->copy()->setTimezone(config('app.timezone'))->toDateTimeString()]);
        } catch (\Throwable $e) {}
        return $latest->copy()->setTimezone(config('app.timezone'))->toDateTimeString();
    }
}
