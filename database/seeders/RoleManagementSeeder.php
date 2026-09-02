<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleManagementSeeder extends Seeder
{
    public function run(): void
    {
        if (!config('database.connections.tenant.database')) {
            config(['database.connections.tenant.database' => env('DB_DATABASE', 'pms_last')]);
            \Illuminate\Support\Facades\DB::purge('tenant');
        }

        $modules = [
            ['name' => 'Dashboard', 'slug' => 'dashboard', 'icon' => 'bx bx-home-smile', 'route_name' => 'dashboard', 'sort_order' => 10],
            ['name' => 'Notifications', 'slug' => 'notifications', 'icon' => 'bx bx-bell', 'route_name' => 'notifications.all', 'sort_order' => 20],
            ['name' => 'Organization', 'slug' => 'organization', 'icon' => 'bx bx-sitemap', 'route_name' => 'organization.index', 'sort_order' => 30],
            ['name' => 'HR', 'slug' => 'hr', 'icon' => 'bx bx-layout', 'sort_order' => 40],
            ['name' => 'Employees', 'slug' => 'employees', 'route_name' => 'employees.index', 'route_prefix' => 'employees', 'sort_order' => 50],
            ['name' => 'Designations', 'slug' => 'designations', 'route_name' => 'designations.index', 'route_prefix' => 'designations', 'sort_order' => 60],
            ['name' => 'Departments', 'slug' => 'departments', 'route_name' => 'parent-departments.index', 'route_prefix' => 'departments', 'sort_order' => 70],
            ['name' => 'Attendance', 'slug' => 'attendance', 'route_name' => 'attendance.index', 'route_prefix' => 'attendance', 'sort_order' => 80],
            ['name' => 'Leave Management', 'slug' => 'leaves', 'route_name' => 'leaves.index', 'route_prefix' => 'leaves', 'sort_order' => 90],
            ['name' => 'Holidays', 'slug' => 'holidays', 'route_name' => 'holidays.calendar', 'route_prefix' => 'holidays', 'sort_order' => 100],
            ['name' => 'Recognition', 'slug' => 'awards', 'route_name' => 'awards.index', 'route_prefix' => 'awards', 'sort_order' => 110],
            ['name' => 'Recruitment', 'slug' => 'recruitment', 'route_name' => 'recruitment.index', 'route_prefix' => 'recruitment', 'sort_order' => 115],
            ['name' => 'Appraisal', 'slug' => 'appraisal', 'route_name' => 'appraisal.index', 'route_prefix' => 'appraisal', 'sort_order' => 118],
            ['name' => 'Reports', 'slug' => 'reports', 'icon' => 'bx bx-bar-chart-alt', 'sort_order' => 120],
            ['name' => 'Work', 'slug' => 'work', 'icon' => 'bx bx-store', 'route_name' => 'projects.index', 'sort_order' => 125],
            ['name' => 'Projects', 'slug' => 'projects', 'route_name' => 'projects.index', 'route_prefix' => 'projects', 'sort_order' => 130],
            ['name' => 'Tasks', 'slug' => 'tasks', 'route_name' => 'tasks.index', 'route_prefix' => 'tasks', 'sort_order' => 140],
            ['name' => 'Timesheets', 'slug' => 'timelogs', 'route_name' => 'timelogs.index', 'route_prefix' => 'timelogs', 'sort_order' => 150],
            ['name' => 'Teams', 'slug' => 'teams', 'route_name' => 'organization.index', 'sort_order' => 160],
            ['name' => 'HR Management', 'slug' => 'hr-management', 'route_name' => 'admin.role-accounts.index', 'sort_order' => 170],
            ['name' => 'Manager Management', 'slug' => 'manager-management', 'route_name' => 'admin.role-accounts.index', 'sort_order' => 180],
            ['name' => 'Payroll', 'slug' => 'payroll', 'icon' => 'bx bx-wallet', 'route_name' => 'payroll.index', 'route_prefix' => 'payroll', 'sort_order' => 190],
            ['name' => 'Settings', 'slug' => 'settings', 'icon' => 'bx bx-cog', 'sort_order' => 200],
            ['name' => 'Role Management', 'slug' => 'role-management', 'route_name' => 'admin.role-permissions.index', 'sort_order' => 210],
            ['name' => 'Permission Management', 'slug' => 'permission-management', 'route_name' => 'admin.role-permissions.index', 'sort_order' => 220],
            ['name' => 'User Management', 'slug' => 'user-management', 'route_name' => 'employees.index', 'sort_order' => 230],
            ['name' => 'Module Management', 'slug' => 'module-management', 'route_name' => 'admin.modules.index', 'sort_order' => 240],
            ['name' => 'System Logs', 'slug' => 'system-logs', 'sort_order' => 250],
            ['name' => 'Activity Logs', 'slug' => 'activity-logs', 'route_name' => 'admin.activities.project', 'sort_order' => 260],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                ['slug' => $module['slug']],
                array_merge(['is_core' => true, 'is_active' => true, 'description' => null], $module)
            );

            try {
                $tenantMod = \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->where('slug', $module['slug'])->first();
                if (! $tenantMod) {
                    \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->insert([
                        'name' => $module['name'],
                        'slug' => $module['slug'],
                        'icon' => $module['icon'] ?? null,
                        'description' => $module['description'] ?? null,
                        'route_prefix' => $module['route_prefix'] ?? null,
                        'route_name' => $module['route_name'] ?? null,
                        'parent_id' => $module['parent_id'] ?? null,
                        'is_core' => 1,
                        'is_active' => 1,
                        'sort_order' => $module['sort_order'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->where('id', $tenantMod->id)->update([
                        'name' => $module['name'],
                        'icon' => $module['icon'] ?? $tenantMod->icon,
                        'route_name' => $module['route_name'] ?? $tenantMod->route_name,
                        'is_core' => 1,
                        'is_active' => 1,
                        'sort_order' => $module['sort_order'] ?? $tenantMod->sort_order,
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {}
        }

        $permissionMap = [
            'admin' => Module::pluck('slug')->all(),
            'manager' => ['dashboard', 'notifications', 'organization', 'teams', 'hr-management', 'employees', 'work', 'projects', 'tasks', 'timelogs', 'attendance', 'leaves', 'reports', 'recruitment', 'appraisal'],
            'hr' => ['dashboard', 'notifications', 'employees', 'attendance', 'leaves', 'work', 'projects', 'tasks', 'timelogs', 'payroll', 'reports', 'recruitment', 'appraisal'],
            'employee' => ['dashboard', 'notifications', 'projects', 'tasks', 'attendance', 'timelogs', 'leaves', 'recruitment', 'appraisal'],
        ];

        $workSlugs = ['work', 'projects', 'tasks', 'timelogs'];
        $tenantModules = \Illuminate\Support\Facades\DB::connection('tenant')->table('modules')->get();

        foreach ($permissionMap as $role => $slugs) {
            foreach ($tenantModules as $module) {
                $enabled = in_array($module->slug, $slugs, true);
                $isWorkModule = in_array($module->slug, $workSlugs, true);
                $isManagerOrHr = in_array($role, ['manager', 'hr'], true);

                RolePermission::updateOrCreate(
                    ['role' => $role, 'module_id' => $module->id],
                    [
                        'can_view' => $enabled,
                        'can_create' => $role === 'admin' || ($enabled && $isManagerOrHr && $isWorkModule),
                        'can_edit' => $role === 'admin' || ($enabled && $isManagerOrHr && $isWorkModule),
                        'can_delete' => $role === 'admin' || ($enabled && $isManagerOrHr && $isWorkModule),
                        'can_approve' => $role === 'admin' || ($enabled && $isManagerOrHr && (in_array($module->slug, ['leaves', 'tasks', 'timelogs'], true))),
                        'can_export' => $role === 'admin' || ($enabled && $isManagerOrHr && ($module->slug === 'reports' || $isWorkModule)),
                        'can_assign' => $role === 'admin' || ($enabled && $isManagerOrHr && in_array($module->slug, ['projects', 'tasks'], true)),
                    ]
                );
            }
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Admin User', 'password' => Hash::make('123456789'), 'role' => 'admin', 'login_allowed' => true, 'is_active' => true, 'email_verified_at' => now()]
        );

        $admin2 = User::updateOrCreate(
            ['email' => 'admin@company.com'],
            ['name' => 'Admin User', 'password' => Hash::make('Admin@123456'), 'role' => 'admin', 'login_allowed' => true, 'is_active' => true, 'email_verified_at' => now()]
        );

        $manager = User::updateOrCreate(
            ['email' => 'manager@company.com'],
            ['name' => 'Manager User', 'password' => Hash::make('Manager@123456'), 'role' => 'manager', 'login_allowed' => true, 'is_active' => true, 'email_verified_at' => now()]
        );

        $hr = User::updateOrCreate(
            ['email' => 'hr@company.com'],
            ['name' => 'HR User', 'password' => Hash::make('Hr@123456'), 'role' => 'hr', 'login_allowed' => true, 'is_active' => true, 'email_verified_at' => now()]
        );

        $employee = User::updateOrCreate(
            ['email' => 'employee@company.com'],
            [
                'name' => 'Employee User',
                'password' => Hash::make('Employee@123456'),
                'role' => 'employee',
                'login_allowed' => true,
                'is_active' => true,
                'email_verified_at' => now(),
                'manager_id' => $manager->id,
                'hr_id' => $hr->id,
                'reports_to_id' => $manager->id,
            ]
        );
    }
}
