<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;

class PlatformAlertReads
{
    public static function ensure(): void
    {
        (require database_path('migrations/central/2026_10_09_210000_create_platform_alert_reads.php'))->up();
    }

    public static function key(array $alert): string
    {
        // Generated IDs and relative timestamps change on each render.
        return hash('sha256', json_encode([
            $alert['category'], $alert['company_id'], $alert['title'], $alert['description'],
        ], JSON_THROW_ON_ERROR));
    }

    public static function apply(array $alerts): array
    {
        self::ensure();
        $keys = DB::connection('central')->table('platform_alert_reads')->pluck('alert_key')->flip();
        foreach ($alerts as &$alert) {
            if ($alert['status'] === 'unread' && $keys->has(self::key($alert))) $alert['status'] = 'read';
        }
        unset($alert);
        return $alerts;
    }

    public static function mark(array $alerts): void
    {
        self::ensure();
        foreach ($alerts as $alert) {
            if ($alert['status'] === 'unread') {
                DB::connection('central')->table('platform_alert_reads')->updateOrInsert(
                    ['alert_key' => self::key($alert)], ['read_at' => now()]
                );
            }
        }
    }
}
