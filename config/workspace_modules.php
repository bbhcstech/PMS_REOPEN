<?php

// Sidebar labels and hierarchy. Slugs remain stable permission identifiers.
// New subdivisions inherit their original module until explicitly configured.
$items = [
    ['dashboard', 'Dashboard', null, 'dashboard'],
    ['company-assistant', 'Company Assistant', null, 'company.ai.index'],
    ['notifications', 'Notifications', null, 'notifications.all'],
    ['my-documents', 'My Documents', null, 'my-documents.index'],
    ['my-payslips', 'My Payslips', null, 'my-payslips.index'],
    ['organization', 'Organization', null, 'organization.index'],
    ['events', 'Events', null, 'events.index'],
    ['community', 'Community', null, 'community.index'],
    ['upper-level-employees', 'Add Upper Level Employee', null, 'admin.upper-level-employees.index'],
    ['hr', 'HR', null, null],
    ['employees', 'Employee', 'hr', 'employees.index'],
    ['recruitment', 'Recruitment', 'hr', 'recruitment.index'],
    ['appraisal', 'Appraisal', 'hr', 'appraisal.index'],
    ['designations', 'Designation', 'hr', 'designations.index'],
    ['departments', 'Department', 'hr', null],
    ['department-parent', 'Parent Department', 'departments', 'parent-departments.index', true],
    ['department-sub', 'Sub Department', 'departments', 'departments.index', true],
    ['higher-level-attendance', 'Higher-level attendance', 'hr', 'admin.authority-attendance', true, 'attendance'],
    ['higher-level-leaves', 'Higher-level leaves', 'hr', 'admin.authority-leaves', true, 'leaves'],
    ['attendance', 'Attendance', 'hr', 'attendance.index'],
    ['leaves', 'My Leaves', 'hr', 'leaves.index'],
    ['leaves-apply', 'Apply Leave', 'hr', 'leaves.create', true, 'leaves'],
    ['holidays', 'Holiday List', 'hr', 'holidays.calendar'],
    ['awards', 'Recognition', 'hr', 'awards.index'],
    ['letterhead', 'Letter Head', 'hr', 'letterhead.index'],
    ['reports', 'Reports', null, null],
    ['reports-task', 'Task Report', 'reports', 'reports.task', true],
    ['reports-timelog', 'Time Log Report', 'reports', 'reports.timelog', true],
    ['reports-finance', 'Finance Report', 'reports', 'reports.finance', true],
    ['reports-income-vs-expense', 'Income Vs Expense', 'reports', 'reports.income-vs-expense', true],
    ['reports-leave', 'Leave Report', 'reports', 'admin.leave.report', true],
    ['reports-attendance', 'Attendance Report', 'reports', 'attendance.report', true],
    ['reports-expense', 'Expense Report', 'reports', 'reports.expense', true],
    ['reports-deal', 'Deal Report', 'reports', 'reports.deal', true],
    ['reports-sales', 'Sales Report', 'reports', 'reports.sales', true],
    ['collaborating-companies', 'Collab Companies', null, 'collaborating-companies.index'],
    ['clients', 'Client', null, 'clients.index'],
    ['work', 'Work', null, null],
    ['projects', 'Projects', 'work', 'projects.index'],
    ['tasks', 'Tasks', 'work', 'tasks.index'],
    ['timelogs', 'Timesheet', 'work', 'timelogs.index'],
    ['leads', 'Leads', null, null],
    ['leads-contacts', 'Lead Contact', 'leads', 'leads.contacts.index'],
    ['crm-deals', 'Deals', 'leads', 'admin.deals.index'],
    ['products', 'Products', null, 'products.index'],
    ['orders', 'Orders', null, 'orders.index'],
    ['tickets', 'Ticket', null, 'tickets.index'],
    ['company-complaints', 'Platform Support & Complaints', null, 'admin.company-complaints.index'],
    ['payroll', 'Payroll', null, 'payroll.index'],
    ['salary-structures', 'Salary Structure', 'payroll', 'payroll.salary-structures.index', true],
    ['employee-salary', 'Employee Salary', 'payroll', 'payroll.employee-salary.index', true],
    ['payroll-processing', 'Processing', 'payroll', 'payroll.processing', true],
    ['payroll-history', 'History', 'payroll', 'payroll.history', true],
    ['payslips', 'Payslips', 'payroll', 'payroll.payslips.index', true],
    ['formula-builder', 'Formulas & Rules', 'payroll', 'payroll.formulas.index', true],
    ['settings', 'Settings', null, null],
    ['settings-dashboard', 'Settings Dashboard', 'settings', 'admin.settings.index'],
    ['company-profile-settings', 'Company Profile', 'settings', 'settings.company'],
    ['organization-details-settings', 'Organization Details', 'settings', 'admin.settings.organization-details'],
    ['business-address-settings', 'Branches / Locations', 'settings', 'admin.settings.business-address.index'],
    ['work-schedule-settings', 'Work Schedule', 'settings', 'admin.settings.work-schedule'],
    ['leave-settings', 'Leave Settings', 'settings', 'admin.settings.leave'],
    ['holiday-settings', 'Holiday Settings', 'settings', 'holidays.index'],
    ['attendance-settings', 'Attendance Settings', 'settings', 'attendance.settings'],
    ['recruitment-settings', 'Recruitment Settings', 'settings', 'admin.settings.recruitment'],
    ['performance-settings', 'Performance Settings', 'settings', 'admin.settings.performance'],
    ['notification-settings', 'Notification Settings', 'settings', 'admin.settings.notification'],
    ['email-settings', 'Email Settings', 'settings', 'admin.settings.email'],
    ['document-settings', 'Document Settings', 'settings', 'admin.settings.document'],
    ['security-settings', 'Security Settings', 'settings', 'admin.settings.security'],
    ['change-password-settings', 'Change Password', 'settings', 'admin.settings.change-password'],
    ['role-permissions-settings', 'Role & Permission', 'settings', 'admin.role-permissions.index'],
    ['localization-settings', 'Localization', 'settings', 'admin.settings.localization'],
    ['terms-policy-settings', 'Terms & Policy', 'settings', 'admin.settings.terms-policy'],
    ['module-management', 'Module Management', 'settings', 'admin.modules.index'],
];

$modules = [];
foreach ($items as $position => $item) {
    [$slug, $name, $parent, $route] = $item;
    $modules[$slug] = [
        'name' => $name, 'parent' => $parent, 'route' => $route,
        'order' => $position, 'inherits' => ($item[4] ?? false) ? ($item[5] ?? $parent) : null,
    ];
}
$modules['salary-structures']['route_prefixes'] = ['payroll.salary-structures'];
$modules['employee-salary']['route_prefixes'] = ['payroll.employee-salary'];
$modules['payroll-processing']['route_names'] = [
    'payroll.processing', 'payroll.calculate', 'payroll.preview', 'payroll.line-input.update',
    'payroll.review', 'payroll.approve', 'payroll.finalize', 'payroll.recalculate', 'payroll.export',
];
$modules['payroll-history']['route_names'] = ['payroll.history'];
$modules['leaves-apply']['route_names'] = ['leaves.store'];
$modules['leaves']['route_names'] = ['leaves.mine'];
$modules['department-parent']['route_prefixes'] = ['parent-departments', 'admin.settings.parent-departments'];
$modules['department-sub']['route_prefixes'] = ['departments', 'admin.settings.departments'];
$modules['designations']['route_prefixes'] = ['designations', 'admin.settings.designations'];
// Settings shortcuts manage the same features as their HR workspace pages.
$modules['department-parent']['route_names'] = ['admin.settings.parent-departments.index'];
$modules['department-sub']['route_names'] = ['admin.settings.departments.index'];
$modules['designations']['route_names'] = ['admin.settings.designations.index'];
$modules['payslips']['route_prefixes'] = ['payroll.payslips', 'payroll.payslip'];
$modules['payslips']['route_names'] = ['payroll.generate-payslips'];
$modules['formula-builder']['route_prefixes'] = ['payroll.formulas'];
// Every route of these features (not only the sidebar page) follows the module's role permission.
$modules['company-assistant']['route_prefixes'] = ['company.ai'];
$modules['my-documents']['route_prefixes'] = ['my-documents'];
$modules['my-payslips']['route_prefixes'] = ['my-payslips'];
$modules['events']['route_prefixes'] = ['events'];
$modules['community']['route_prefixes'] = ['community'];
$modules['recruitment']['route_prefixes'] = ['recruitment'];
$modules['appraisal']['route_prefixes'] = ['appraisal'];
$modules['leads-contacts']['route_prefixes'] = ['leads.contacts'];
$modules['company-complaints']['route_prefixes'] = ['admin.company-complaints'];
return $modules;
