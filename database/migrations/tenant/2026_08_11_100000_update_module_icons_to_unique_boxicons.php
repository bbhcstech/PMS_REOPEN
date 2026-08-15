<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Assign a unique, semantically relevant Boxicons icon to every module
     * based on its slug.
     */
    public function up(): void
    {
        $icons = [
            // Core navigation
            'dashboard'            => 'bx bx-tachometer',
            'notifications'        => 'bx bx-bell',
            'organization'         => 'bx bx-sitemap',
            'settings'             => 'bx bx-cog',

            // People / HR
            'hr-employees'         => 'bx bx-group',
            'hr'                   => 'bx bx-user-pin',
            'hr-management'        => 'bx bx-user-check',
            'employees'            => 'bx bx-id-card',
            'designations'         => 'bx bx-badge-check',
            'departments'          => 'bx bx-building-house',
            'teams'                => 'bx bx-network-chart',
            'user-management'      => 'bx bx-user-detail',
            'manager-management'   => 'bx bx-briefcase',

            // Time & Attendance
            'attendance'           => 'bx bx-fingerprint',
            'timelogs'             => 'bx bx-stopwatch',
            'holidays'             => 'bx bx-calendar-star',

            // Leave
            'leaves'               => 'bx bx-calendar-x',

            // Projects & Tasks
            'projects'             => 'bx bx-task',
            'tasks'                => 'bx bx-check-square',

            // Finance / Payroll
            'payroll'              => 'bx bx-money-withdraw',

            // CRM / Deals
            'crm-deals'            => 'bx bx-handshake',

            // Documents / Contracts
            'contracts'            => 'bx bx-file-blank',

            // Support
            'tickets'              => 'bx bx-support',

            // Recognition
            'awards'               => 'bx bx-trophy',

            // Reports & Analytics
            'reports'              => 'bx bx-bar-chart-alt-2',

            // Access & Security
            'role-management'      => 'bx bx-shield-quarter',
            'permission-management' => 'bx bx-key',
            'module-management'    => 'bx bx-layer',

            // Logs
            'system-logs'          => 'bx bx-list-ul',
            'activity-logs'        => 'bx bx-pulse',
        ];

        foreach ($icons as $slug => $icon) {
            DB::table('modules')
                ->where('slug', $slug)
                ->update(['icon' => $icon]);
        }
    }

    public function down(): void
    {
        // Revert to generic fallback
        DB::table('modules')->update(['icon' => 'bx bx-cube']);
    }
};
