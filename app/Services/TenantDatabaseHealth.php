<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Live connectivity check for tenant databases (System Health page).
 * Uses its own connection so the request's `tenant` connection is never repointed.
 */
class TenantDatabaseHealth
{
    private const PROBE = 'tenant_health_probe';
    private const SLOW_MS = 250;
    private const STORAGE_WARNING_PCT = 80;

    public function check($company): array
    {
        $dbName = (string) ($company->db_name ?? '');
        $checkedAt = now();
        $result = [
            'connected'   => false,
            'status'      => 'down',
            'latency_ms'  => null,
            'tables'      => null,
            'size_mb'     => null,
            'storage_pct' => null,
            'quota_mb'    => (int) ($company->max_storage_mb ?? 0),
            'error'       => null,
            'checked_at'  => $checkedAt,
        ];

        if ($dbName === '') {
            $result['error'] = 'No database configured for this tenant.';
            return $result;
        }

        try {
            config(['database.connections.' . self::PROBE => array_merge(
                config('database.connections.tenant') ?? config('database.connections.' . config('database.default')),
                ['database' => $dbName]
            )]);
            DB::purge(self::PROBE);
            $connection = DB::connection(self::PROBE);

            $started = microtime(true);
            $connection->select('SELECT 1');
            $result['latency_ms'] = round((microtime(true) - $started) * 1000, 1);
            $result['connected'] = true;

            $stats = $connection->selectOne(
                'SELECT COUNT(*) AS tables_count, COALESCE(SUM(data_length + index_length), 0) AS size_bytes
                 FROM information_schema.TABLES WHERE table_schema = ?',
                [$dbName]
            );
            $result['tables'] = (int) ($stats->tables_count ?? 0);
            $result['size_mb'] = round(((int) ($stats->size_bytes ?? 0)) / 1048576, 2);
            if ($result['quota_mb'] > 0) {
                $result['storage_pct'] = min(100, (int) round(100 * $result['size_mb'] / $result['quota_mb']));
            }

            $slow = $result['latency_ms'] > self::SLOW_MS;
            $full = $result['storage_pct'] !== null && $result['storage_pct'] >= self::STORAGE_WARNING_PCT;
            $result['status'] = $slow || $full ? 'warning' : 'healthy';
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
        } finally {
            DB::purge(self::PROBE);
        }

        return $result;
    }

    /**
     * Central database pool figures from the MySQL server itself.
     */
    public function poolStats(string $connection = 'central'): array
    {
        try {
            $db = DB::connection($connection);
            $started = microtime(true);
            $db->select('SELECT 1');
            $ping = round((microtime(true) - $started) * 1000, 1);
            $threads = (int) ($db->selectOne("SHOW GLOBAL STATUS LIKE 'Threads_connected'")->Value ?? 0);
            $max = (int) ($db->selectOne("SHOW VARIABLES LIKE 'max_connections'")->Value ?? 0);

            return [
                'active'   => $threads,
                'max'      => $max,
                'load_pct' => $max > 0 ? (int) round(100 * $threads / $max) : null,
                'ping_ms'  => $ping,
            ];
        } catch (\Throwable $e) {
            return ['active' => null, 'max' => null, 'load_pct' => null, 'ping_ms' => null];
        }
    }

    /**
     * Row payload shared by the page render and the live refresh endpoint.
     */
    public function row($company): array
    {
        $check = $this->check($company);

        return [
            'company_id'     => $company->id,
            'status'         => $check['status'],
            'connection'     => $check['connected'] ? 'Connected' : 'Unreachable',
            'connected'      => $check['connected'],
            'latency_ms'     => $check['latency_ms'] !== null ? $check['latency_ms'] . ' ms' : '—',
            'storage_pct'    => $check['storage_pct'] !== null ? $check['storage_pct'] . '%' : ($check['size_mb'] !== null ? $check['size_mb'] . ' MB' : '—'),
            'storage_detail' => $check['size_mb'] !== null
                ? $check['size_mb'] . ' MB' . ($check['quota_mb'] > 0 ? ' of ' . $check['quota_mb'] . ' MB' : ' (no quota)')
                : null,
            'tables'         => $check['tables'],
            'error'          => $check['error'],
            'last_check'     => $check['checked_at']->diffForHumans(),
            'last_check_iso' => $check['checked_at']->toIso8601String(),
            'last_check_at'  => $check['checked_at']->format('d M Y, h:i:s A'),
        ];
    }
}
