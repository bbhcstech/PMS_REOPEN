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

        $plans = [];
        foreach (\App\Support\SupportedPlans::defaults() as $slug => $values) {
            $plans[] = $values + [
                'slug' => $slug,
                'sort_order' => array_search($slug, \App\Support\SupportedPlans::SLUGS, true) + 1,
                'modules' => $slug === 'free' ? ['projects', 'hr-employees'] : $modules->pluck('slug')->all(),
            ];
        }

        foreach ($plans as $planData) {
            $moduleSlugs = $planData['modules'];
            unset($planData['modules']);

            $plan = SubscriptionPlan::firstOrCreate(
                ['slug' => $planData['slug']],
                $planData + ['is_active' => true]
            );

            $plan->modules()->syncWithoutDetaching($modules->whereIn('slug', $moduleSlugs)->pluck('id')->all());
        }
    }
}
