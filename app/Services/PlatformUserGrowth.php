<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlatformUserGrowth
{
    public function data(Collection $companies): array
    {
        $daily = [];
        $unavailable = 0;
        $connection = 'platform_user_growth';
        try {
            foreach ($companies->pluck('db_name')->filter()->unique() as $database) {
                config(['database.connections.'.$connection => array_replace(config('database.connections.tenant'), ['database' => $database])]);
                DB::purge($connection);
                try {
                    $columns = Schema::connection($connection)->getColumnListing('users');
                    $query = DB::connection($connection)->table('users')->whereNotNull('created_at');
                    if (in_array('deleted_at', $columns, true)) $query->whereNull('deleted_at');
                    $active = in_array('is_active', $columns, true) ? 'SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END)' : '0';
                    foreach ($query->selectRaw('DATE(created_at) as date, COUNT(*) as total, '.$active.' as active')->groupByRaw('DATE(created_at)')->get() as $row) {
                        $daily[$row->date] ??= ['date' => $row->date, 'total' => 0, 'active' => 0];
                        $daily[$row->date]['total'] += (int) $row->total;
                        $daily[$row->date]['active'] += (int) $row->active;
                    }
                } catch (\Throwable $e) {
                    $unavailable++;
                }
            }
        } finally {
            DB::purge($connection);
            config(['database.connections.'.$connection => null]);
        }
        ksort($daily);
        return ['records' => array_values($daily), 'today' => now()->format('Y-m-d'), 'unavailable' => $unavailable];
    }
}
