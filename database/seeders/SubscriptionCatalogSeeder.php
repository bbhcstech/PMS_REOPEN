<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $modules = collect([
            ['name' => 'Projects', 'slug' => 'projects', 'icon' => 'folder-kanban', 'description' => 'Project planning, tasks, milestones, and files.', 'route_prefix' => 'projects', 'is_core' => true, 'sort_order' => 10],
            ['name' => 'HR & Employees', 'slug' => 'hr-employees', 'icon' => 'users', 'description' => 'Departments, designations, employee profiles, and awards.', 'route_prefix' => 'employees', 'is_core' => true, 'sort_order' => 20],
            ['name' => 'Attendance', 'slug' => 'attendance', 'icon' => 'calendar-check', 'description' => 'Clock-in, location tracking, reports, and exports.', 'route_prefix' => 'attendance', 'is_core' => false, 'sort_order' => 30],
            ['name' => 'Leaves', 'slug' => 'leaves', 'icon' => 'calendar-days', 'description' => 'Leave policies, balances, requests, and approvals.', 'route_prefix' => 'leaves', 'is_core' => false, 'sort_order' => 40],
            ['name' => 'Tickets', 'slug' => 'tickets', 'icon' => 'life-buoy', 'description' => 'Support tickets, agents, groups, and replies.', 'route_prefix' => 'tickets', 'is_core' => false, 'sort_order' => 50],
            ['name' => 'CRM Deals', 'slug' => 'crm-deals', 'icon' => 'handshake', 'description' => 'Leads, contacts, deal stages, and follow-ups.', 'route_prefix' => 'admin/deals', 'is_core' => false, 'sort_order' => 60],
            ['name' => 'Contracts', 'slug' => 'contracts', 'icon' => 'file-signature', 'description' => 'Contract templates, approvals, and signing.', 'route_prefix' => 'admin/contracts', 'is_core' => false, 'sort_order' => 70],
        ])->map(function ($module) {
            return Module::updateOrCreate(
                ['slug' => $module['slug']],
                $module + ['is_active' => true]
            );
        });

        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For small teams starting with core project and HR workflows.',
                'monthly_price' => 49,
                'yearly_price' => 499,
                'max_users' => 15,
                'max_projects' => 20,
                'max_clients' => 50,
                'max_storage_mb' => 2048,
                'features' => ['Core dashboards', 'Projects', 'Employee records', 'Email support'],
                'sort_order' => 10,
                'modules' => ['projects', 'hr-employees'],
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing companies that need attendance, leaves, tickets, and reports.',
                'monthly_price' => 149,
                'yearly_price' => 1499,
                'max_users' => 75,
                'max_projects' => 150,
                'max_clients' => 300,
                'max_storage_mb' => 10240,
                'features' => ['Everything in Starter', 'Attendance', 'Leave management', 'Support tickets'],
                'sort_order' => 20,
                'modules' => ['projects', 'hr-employees', 'attendance', 'leaves', 'tickets'],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For larger organizations with CRM, contracts, and premium controls.',
                'monthly_price' => 399,
                'yearly_price' => 3999,
                'max_users' => 500,
                'max_projects' => 1000,
                'max_clients' => 2000,
                'max_storage_mb' => 102400,
                'features' => ['Everything in Professional', 'CRM deals', 'Contracts', 'Priority support'],
                'sort_order' => 30,
                'modules' => ['projects', 'hr-employees', 'attendance', 'leaves', 'tickets', 'crm-deals', 'contracts'],
            ],
        ];

        foreach ($plans as $planData) {
            $moduleSlugs = $planData['modules'];
            unset($planData['modules']);

            $plan = SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData + ['is_active' => true]
            );

            $plan->modules()->sync($modules->whereIn('slug', $moduleSlugs)->pluck('id')->all());
        }
    }
}
