<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settingsGroups = [
            'company-profile' => [
                'name' => 'Company Profile',
                'description' => 'Company name, logo, address, phone, email, website',
                'icon' => 'bx bx-building',
                'color' => 'primary',
                'route' => 'settings.company',
                'category' => 'Business'
            ],
            'organization-details' => [
                'name' => 'Organization Details',
                'description' => 'Industry, company size, registration details, tax information',
                'icon' => 'bx bx-detail',
                'color' => 'info',
                'route' => 'admin.settings.organization-details',
                'category' => 'Business'
            ],
            'branches-locations' => [
                'name' => 'Branches / Locations',
                'description' => 'Office locations, branches, addresses',
                'icon' => 'bx bx-map-pin',
                'color' => 'success',
                'route' => 'admin.settings.business-address.index',
                'category' => 'Business'
            ],
            'departments' => [
                'name' => 'Department',
                'description' => 'Select Parent Department or Sub Department to manage hierarchy',
                'icon' => 'bx bx-sitemap',
                'color' => 'warning',
                'category' => 'Organization',
                'has_dropdown' => true,
                'dropdown_options' => [
                    [
                        'name' => 'Parent Department',
                        'description' => 'Manage main organization units',
                        'icon' => 'bx bx-building-house',
                        'route' => 'parent-departments.index'
                    ],
                    [
                        'name' => 'Sub Department',
                        'description' => 'Manage sub-teams & branches',
                        'icon' => 'bx bx-git-repo-forked',
                        'route' => 'departments.index'
                    ]
                ]
            ],
            'designations' => [
                'name' => 'Designations / Job Titles',
                'description' => 'Software Engineer, HR Manager, Team Lead, etc.',
                'icon' => 'bx bx-id-card',
                'color' => 'danger',
                'route' => 'designations.index',
                'category' => 'Organization'
            ],
            'employee-id' => [
                'name' => 'Employee ID Settings',
                'description' => 'Employee ID prefix, numbering format, auto-generation',
                'icon' => 'bx bx-barcode',
                'color' => 'primary',
                'route' => 'admin.settings.employee-id',
                'category' => 'HR'
            ],
            'work-schedule' => [
                'name' => 'Work Schedule',
                'description' => 'Working days, working hours, shifts',
                'icon' => 'bx bx-calendar-event',
                'color' => 'info',
                'route' => 'admin.settings.work-schedule',
                'category' => 'HR'
            ],
            'leave-settings' => [
                'name' => 'Leave Settings',
                'description' => 'Leave types, yearly allowance, carry-forward rules',
                'icon' => 'bx bx-time-five',
                'color' => 'success',
                'route' => 'admin.settings.leave',
                'category' => 'HR'
            ],
            'holiday-settings' => [
                'name' => 'Holiday Settings',
                'description' => 'Public holidays, company holidays',
                'icon' => 'bx bx-sun',
                'color' => 'warning',
                'route' => 'holidays.index',
                'category' => 'HR'
            ],
            'attendance-settings' => [
                'name' => 'Attendance Settings',
                'description' => 'Late threshold, grace period, overtime rules',
                'icon' => 'bx bx-user-check',
                'color' => 'danger',
                'route' => 'attendance.settings',
                'category' => 'HR'
            ],
            'payroll-settings' => [
                'name' => 'Payroll Settings',
                'description' => 'Salary structure, pay cycle, deductions, allowances',
                'icon' => 'bx bx-wallet',
                'color' => 'primary',
                'route' => 'payroll.settings.index',
                'category' => 'Finance'
            ],
            'recruitment-settings' => [
                'name' => 'Recruitment Settings',
                'description' => 'Job categories, recruitment stages, hiring configuration',
                'icon' => 'bx bx-briefcase',
                'color' => 'info',
                'route' => 'admin.settings.recruitment',
                'category' => 'HR'
            ],
            'performance-settings' => [
                'name' => 'Performance Settings',
                'description' => 'Appraisal cycles, rating scales, performance criteria',
                'icon' => 'bx bx-trending-up',
                'color' => 'success',
                'route' => 'admin.settings.performance',
                'category' => 'HR'
            ],
            'notification-settings' => [
                'name' => 'Notification Settings',
                'description' => 'Email/system notifications and reminders',
                'icon' => 'bx bx-bell',
                'color' => 'warning',
                'route' => 'admin.settings.notification',
                'category' => 'System'
            ],
            'email-settings' => [
                'name' => 'Email Settings',
                'description' => 'SMTP/email configuration',
                'icon' => 'bx bx-envelope',
                'color' => 'danger',
                'route' => 'admin.settings.email',
                'category' => 'System'
            ],
            'document-settings' => [
                'name' => 'Document Settings',
                'description' => 'Employee document types and required documents',
                'icon' => 'bx bx-file',
                'color' => 'primary',
                'route' => 'admin.settings.document',
                'category' => 'HR'
            ],
            'security-settings' => [
                'name' => 'Security Settings',
                'description' => 'Password policy, session timeout, 2FA, login restrictions',
                'icon' => 'bx bx-shield-quarter',
                'color' => 'info',
                'route' => 'admin.settings.security',
                'category' => 'Security'
            ],
            'change-password-settings' => [
                'name' => 'Change Password',
                'description' => 'Change HR, Manager, and Employee account passwords with auto-logout alerts',
                'icon' => 'bx bx-key',
                'color' => 'warning',
                'route' => 'admin.settings.change-password',
                'category' => 'Security'
            ],
            'role-permission-settings' => [
                'name' => 'Role & Permission Settings',
                'description' => 'Admin, HR, Manager, Employee permissions',
                'icon' => 'bx bx-lock-alt',
                'color' => 'success',
                'route' => 'admin.role-permissions.index',
                'category' => 'Security'
            ],
            'localization' => [
                'name' => 'Localization',
                'description' => 'Currency, timezone, date format, language',
                'icon' => 'bx bx-globe',
                'color' => 'warning',
                'route' => 'admin.settings.localization',
                'category' => 'System'
            ],
            'system-preferences' => [
                'name' => 'System Preferences',
                'description' => 'General application behavior and defaults',
                'icon' => 'bx bx-slider-alt',
                'color' => 'danger',
                'route' => 'admin.settings.app',
                'category' => 'System'
            ],
        ];

        return view('admin.settings.index', compact('settingsGroups'));
    }
}
