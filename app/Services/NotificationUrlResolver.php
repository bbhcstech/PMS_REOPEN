<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Award;
use App\Models\Client;
use App\Models\EmployeeDetail;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveApologyLetter;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;

class NotificationUrlResolver
{
    public static function resolve(DatabaseNotification|array|null $notification): string
    {
        $data = $notification instanceof DatabaseNotification
            ? ($notification->data ?? [])
            : ($notification ?? []);

        $data = is_array($data) ? $data : [];

        return self::fromPrimaryIdentifiers($data)
            ?? self::fromEntity($data)
            ?? self::fromWorkflowType($data)
            ?? self::fromPeopleIdentifiers($data)
            ?? self::fromStoredUrl($data)
            ?? route('notifications.all');
    }

    private static function fromPrimaryIdentifiers(array $data): ?string
    {
        $candidates = [
            ['task_id', 'tasks.show'],
            ['ticket_id', 'tickets.show'],
            ['project_id', 'projects.show'],
            ['client_id', 'clients.show'],
            ['leave_id', 'leaves.show'],
            ['apology_letter_id', 'leaves.apology-letters.show'],
            ['leave_apology_letter_id', 'leaves.apology-letters.show'],
        ];

        foreach ($candidates as [$key, $route]) {
            $id = data_get($data, $key);

            if ($id && Route::has($route)) {
                return route($route, $id);
            }
        }

        if (data_get($data, 'attendance_id') || data_get($data, 'record_id')) {
            return self::routeIfExists('attendance.index');
        }

        if (data_get($data, 'holiday_id')) {
            return self::routeIfExists('holidays.calendar') ?? self::routeIfExists('holidays.index');
        }

        if (data_get($data, 'award_id')) {
            return self::routeIfExists('awards.index');
        }

        return null;
    }

    private static function fromWorkflowType(array $data): ?string
    {
        $type = (string) data_get($data, 'type', '');

        if (str_contains($type, 'attendance') || $type === 'clock_in' || data_get($data, 'record_id')) {
            return self::routeIfExists('attendance.index');
        }

        if (str_contains($type, 'holiday')) {
            return self::routeIfExists('holidays.calendar') ?? self::routeIfExists('holidays.index');
        }

        if (str_contains($type, 'award') || str_contains($type, 'recognition')) {
            return self::routeIfExists('awards.index');
        }

        return null;
    }

    private static function fromPeopleIdentifiers(array $data): ?string
    {
        foreach (['employee_id', 'user_id', 'assigned_by_id', 'assigned_to_id'] as $key) {
            $id = data_get($data, $key);

            if ($id && Route::has('employees.show')) {
                return route('employees.show', $id);
            }
        }

        return null;
    }

    private static function fromEntity(array $data): ?string
    {
        $entityType = ltrim((string) data_get($data, 'entity_type'), '\\');
        $entityId = data_get($data, 'entity_id');

        if (! $entityType || ! $entityId) {
            return null;
        }

        $map = [
            Task::class => 'tasks.show',
            Ticket::class => 'tickets.show',
            Project::class => 'projects.show',
            Client::class => 'clients.show',
            User::class => 'employees.show',
            EmployeeDetail::class => 'employees.show',
            Leave::class => 'leaves.show',
            LeaveApologyLetter::class => 'leaves.apology-letters.show',
        ];

        if (isset($map[$entityType]) && Route::has($map[$entityType])) {
            return route($map[$entityType], $entityId);
        }

        if ($entityType === Attendance::class) {
            return self::routeIfExists('attendance.index');
        }

        if ($entityType === Holiday::class) {
            return self::routeIfExists('holidays.calendar') ?? self::routeIfExists('holidays.index');
        }

        if ($entityType === Award::class) {
            return self::routeIfExists('awards.index');
        }

        return null;
    }

    private static function fromStoredUrl(array $data): ?string
    {
        $url = trim((string) data_get($data, 'url', ''));

        if ($url === '' || $url === '#') {
            return null;
        }

        if (str_starts_with($url, '/')) {
            return url($url);
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $requestHost = request()?->getHost();
        $urlHost = parse_url($url, PHP_URL_HOST);

        $allowedHosts = array_filter([$appHost, $requestHost]);

        if ($urlHost && $allowedHosts && ! in_array(strtolower($urlHost), array_map('strtolower', $allowedHosts), true)) {
            return null;
        }

        return $url;
    }

    private static function routeIfExists(string $route): ?string
    {
        return Route::has($route) ? route($route) : null;
    }
}
