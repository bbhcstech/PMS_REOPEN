<?php

namespace App\Services;

/** The same defaults drive the editor and server-side authorization. */
class RolePermissionDefaults
{
    private array $permissions = ['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'];
    public function getDefaultPermissionsForRole(string $role, string $slug): array
    {
        if ($role === 'admin') {
            return $this->permissions;
        }

        if (in_array($slug, ['hr-management', 'manager-management', 'role-management',
            'permission-management', 'role-permissions-settings', 'module-management'], true)) return [];
        if ($role === 'employee' && in_array($slug, ['user-management', 'settings-dashboard', 'settings',
            'payroll-settings', 'security-settings', 'localization-settings', 'terms-policy-settings',
            'activity-logs', 'system-logs'], true)) return [];

        $hrFullModules = [
            'dashboard', 'notifications', 'my-documents', 'organization', 'employees',
            'hr-employees', 'departments', 'designations', 'attendance', 'leaves',
            'leave-management', 'holidays', 'recognition', 'awards', 'recruitment',
            'appraisal', 'work', 'projects', 'tasks', 'timesheets', 'timelogs',
            'payroll', 'payroll-architectures', 'payslips', 'salary-structures',
            'payroll-cycles', 'payroll-policies', 'formula-builder', 'deduction-rules',
            'bonus-rules', 'tax-rules', 'overtime-rules', 'payroll-reports', 'expenses',
            'billing', 'reports', 'analytics', 'advanced-reports', 'events', 'community',
            'collaborating-companies', 'clients', 'leads-contacts', 'tickets',
            'products', 'orders',
        ];

        $managerFullModules = [
            'dashboard', 'notifications', 'my-documents', 'organization', 'teams',
            'work', 'projects', 'tasks', 'timesheets', 'timelogs', 'attendance',
            'leaves', 'leave-management', 'reports', 'analytics', 'recruitment',
            'appraisal', 'recognition', 'awards', 'events', 'community',
            'collaborating-companies', 'clients', 'leads-contacts', 'tickets',
            'products', 'orders',
        ];

        $employeeModules = [
            'dashboard', 'notifications', 'my-documents', 'attendance', 'leaves',
            'leave-management', 'holidays', 'recognition', 'awards', 'work',
            'projects', 'tasks', 'timesheets', 'timelogs', 'payslips', 'events',
            'community', 'products', 'orders', 'recruitment', 'appraisal',
        ];

        if ($role === 'hr') {
            if (in_array($slug, $hrFullModules, true)) {
                return ['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'];
            }
            return ['view'];
        }

        if ($role === 'manager') {
            if (in_array($slug, $managerFullModules, true)) {
                return ['view', 'create', 'edit', 'delete', 'approve', 'export', 'assign'];
            }
            if (in_array($slug, ['employees', 'hr-employees'], true)) {
                return ['view', 'export'];
            }
            return ['view'];
        }

        if ($role === 'employee') {
            if (in_array($slug, ['my-documents', 'timesheets', 'timelogs', 'leaves', 'leave-management', 'attendance', 'tasks', 'community'], true)) {
                return ['view', 'create', 'edit'];
            }
            if (in_array($slug, $employeeModules, true)) {
                return ['view'];
            }
            return [];
        }

        return [];
    }

}
