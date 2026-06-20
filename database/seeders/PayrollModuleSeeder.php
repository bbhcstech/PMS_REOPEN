<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\PayrollArchitecture;
use App\Models\PayrollArchitectureVersion;
use App\Models\PayslipTemplate;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class PayrollModuleSeeder extends Seeder
{
    public function run(): void
    {
        $parent = Module::updateOrCreate(
            ['slug' => 'payroll'],
            [
                'name' => 'Payroll',
                'icon' => 'bx bx-wallet',
                'description' => 'Enterprise payroll processing, payslips, rules, reports, and audit history.',
                'route_name' => 'payroll.index',
                'route_prefix' => 'payroll',
                'is_core' => true,
                'is_active' => true,
                'sort_order' => 190,
            ]
        );

        $modules = [
            ['Payroll Architectures', 'payroll-architectures', 'payroll.architectures.index', 191],
            ['Payslips', 'payslips', 'payroll.payslips.index', 192],
            ['Salary Structures', 'salary-structures', 'payroll.salary-structures.index', 193],
            ['Payroll Policies', 'payroll-policies', 'payroll.policies.index', 194],
            ['Payroll Cycles', 'payroll-cycles', 'payroll.cycles.index', 195],
            ['Tax Rules', 'tax-rules', 'payroll.tax-rules.index', 196],
            ['Bonus Rules', 'bonus-rules', 'payroll.bonus-rules.index', 197],
            ['Deduction Rules', 'deduction-rules', 'payroll.deduction-rules.index', 198],
            ['Overtime Rules', 'overtime-rules', 'payroll.overtime-rules.index', 199],
            ['Payroll Reports', 'payroll-reports', 'payroll.reports.index', 200],
            ['Payroll Audit Logs', 'payroll-audit-logs', 'payroll.audit-logs.index', 201],
            ['Payroll Settings', 'payroll-settings', 'payroll.settings.index', 202],
            ['Import Export', 'payroll-import-export', 'payroll.import-export.index', 203],
            ['Payroll Archive', 'payroll-archive', 'payroll.archive.index', 204],
            ['Formula Builder', 'formula-builder', 'payroll.formula-builder.index', 205],
        ];

        foreach ($modules as [$name, $slug, $routeName, $sortOrder]) {
            Module::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'icon' => 'bx bx-chevron-right',
                    'description' => $name . ' payroll module',
                    'route_name' => $routeName,
                    'route_prefix' => 'payroll',
                    'parent_id' => $parent->id,
                    'is_core' => false,
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ]
            );
        }

        $allPayrollSlugs = Module::where('slug', 'payroll')
            ->orWhere('parent_id', $parent->id)
            ->pluck('slug')
            ->all();

        $roleRules = [
            'admin' => [
                'slugs' => $allPayrollSlugs,
                'flags' => ['can_view', 'can_create', 'can_edit', 'can_delete', 'can_approve', 'can_export', 'can_assign'],
            ],
            'manager' => [
                'slugs' => ['payroll', 'payslips', 'bonus-rules', 'overtime-rules', 'payroll-reports'],
                'flags' => ['can_view', 'can_approve', 'can_export'],
            ],
            'hr' => [
                'slugs' => ['payroll', 'payslips', 'salary-structures', 'payroll-cycles', 'deduction-rules', 'bonus-rules', 'overtime-rules', 'tax-rules', 'payroll-reports', 'payroll-import-export'],
                'flags' => ['can_view', 'can_create', 'can_edit', 'can_export'],
            ],
            'employee' => [
                'slugs' => ['payroll', 'payslips'],
                'flags' => ['can_view'],
            ],
        ];

        foreach (['admin', 'manager', 'hr', 'employee'] as $role) {
            foreach (Module::whereIn('slug', $allPayrollSlugs)->get() as $module) {
                $enabled = in_array($module->slug, $roleRules[$role]['slugs'], true);
                $flags = $enabled ? $roleRules[$role]['flags'] : [];

                RolePermission::updateOrCreate(
                    ['role' => $role, 'module_id' => $module->id],
                    [
                        'can_view' => in_array('can_view', $flags, true),
                        'can_create' => in_array('can_create', $flags, true),
                        'can_edit' => in_array('can_edit', $flags, true),
                        'can_delete' => in_array('can_delete', $flags, true),
                        'can_approve' => in_array('can_approve', $flags, true),
                        'can_export' => in_array('can_export', $flags, true),
                        'can_assign' => in_array('can_assign', $flags, true),
                    ]
                );
            }
        }

        $architecture = PayrollArchitecture::firstOrCreate(
            ['code' => 'standard-payroll'],
            [
                'name' => 'Standard Payroll',
                'type' => 'standard',
                'description' => 'Default monthly payroll architecture.',
                'is_active' => true,
                'effective_date' => now()->toDateString(),
                'version' => 1,
                'settings' => ['workflow' => ['draft', 'generated', 'reviewed_by_hr', 'approved_by_manager', 'approved_by_admin', 'finalized', 'payslip_generated', 'locked']],
            ]
        );

        PayrollArchitecture::where('id', '!=', $architecture->id)->where('is_active', true)->update(['is_active' => false]);

        PayrollArchitectureVersion::firstOrCreate(
            ['payroll_architecture_id' => $architecture->id, 'version' => 1],
            ['snapshot' => $architecture->toArray(), 'effective_date' => $architecture->effective_date]
        );

        foreach (['classic', 'modern', 'corporate', 'minimal', 'custom'] as $template) {
            PayslipTemplate::firstOrCreate(
                ['template_type' => $template],
                [
                    'name' => ucfirst($template),
                    'content' => ['sections' => ['company', 'employee', 'attendance', 'earnings', 'deductions', 'net_salary', 'signature']],
                    'is_active' => $template === 'classic',
                    'version' => 1,
                    'effective_date' => now()->toDateString(),
                ]
            );
        }
    }
}
