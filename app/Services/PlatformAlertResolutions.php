<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Persists "Resolve" on the Super Admin Alerts page for both stored notifications
 * and generated platform alerts, keyed by a stable alert key (see alertKey()).
 */
class PlatformAlertResolutions
{
    private static bool $ensured = false;

    public static function ensure(): void
    {
        if (self::$ensured) {
            return;
        }

        (require database_path('migrations/central/2026_10_09_220000_create_platform_alert_resolutions.php'))->up();
        self::$ensured = true;
    }

    /**
     * Stable identifier for an alert across renders: stored notifications use their record id,
     * generated alerts use the same content hash as PlatformAlertReads.
     */
    public static function alertKey(array $alert): string
    {
        return isset($alert['notification_record_id'])
            ? 'notif-' . $alert['notification_record_id']
            : PlatformAlertReads::key($alert);
    }

    public static function apply(array $alerts): array
    {
        self::ensure();
        $resolved = DB::connection('central')->table('platform_alert_resolutions')->pluck('alert_key')->flip();

        foreach ($alerts as &$alert) {
            if ($resolved->has(self::alertKey($alert))) {
                $alert['status'] = 'resolved';
            }
        }
        unset($alert);

        return $alerts;
    }

    public static function mark(array $alert): void
    {
        self::ensure();
        DB::connection('central')->table('platform_alert_resolutions')->updateOrInsert(
            ['alert_key' => self::alertKey($alert)],
            ['resolved_at' => now()]
        );
    }
}
