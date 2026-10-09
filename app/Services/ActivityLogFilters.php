<?php
namespace App\Services;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogFilters
{
    public static function apply(Collection $events, Request $request): Collection
    {
        return $events->filter(function ($event) use ($request) {
            foreach (['actor' => 'role', 'company' => 'company_id', 'result' => 'status'] as $filter => $field) {
                if ($request->filled($filter) && (string) $event[$field] !== (string) $request->query($filter)) return false;
            }
            $module = $request->query('module');
            if ($module && ($event['module'] === 'User Management' ? 'Users' : $event['module']) !== $module) return false;
            $action = $request->query('action');
            if ($action === 'Security') {
                if (!$event['is_security']) return false;
            } elseif ($action && $event['action_type'] !== $action && !str_contains(strtolower($event['action']), strtolower($action))) return false;
            $search = strtolower(trim((string) $request->query('search', '')));
            if ($search && !str_contains(strtolower(implode(' ', array_map(fn ($key) => $event[$key] ?? '', ['user_name', 'user_email', 'company_name', 'action', 'resource', 'ip_address']))), $search)) return false;
            $date = Carbon::parse($event['timestamp']);
            return match ($request->query('date')) {
                'today' => $date->isToday(),
                'yesterday' => $date->isYesterday(),
                '7days' => $date->betweenIncluded(now()->subDays(6)->startOfDay(), now()),
                '30days' => $date->betweenIncluded(now()->subDays(29)->startOfDay(), now()),
                default => true,
            };
        })->values();
    }
}
