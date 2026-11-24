<?php

namespace App\Http\Controllers\SuperAdmin\Concerns;

use App\Models\Central\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

trait ManagesPlanCatalog
{
    /**
     * Display Subscription Plans Catalog workspace.
     */
    public function plans(Request $request): View
    {
        try {
            $plans = \App\Models\Central\Plan::on('central')->standard()->orderBy('monthly_price', 'asc')->orderBy('id')->get();
        } catch (\Throwable $e) {
            $plans = collect();
        }

        if ($plans->isEmpty()) {
            $defaultPlans = [
                ['name' => 'FREE', 'slug' => 'free', 'description' => 'Essential features for small teams and trial accounts.', 'monthly_price' => 0, 'yearly_price' => 0, 'max_users' => 5, 'max_storage_mb' => 5120, 'is_active' => true, 'sort_order' => 1],
                ['name' => 'GOLD', 'slug' => 'gold', 'description' => 'Popular for growing businesses needing team collaboration.', 'monthly_price' => 4999, 'yearly_price' => 49990, 'max_users' => 25, 'max_storage_mb' => 25600, 'is_active' => true, 'sort_order' => 2],
                ['name' => 'PLATINUM', 'slug' => 'platinum', 'description' => 'Advanced capabilities for scaling enterprise organizations.', 'monthly_price' => 9999, 'yearly_price' => 99990, 'max_users' => 100, 'max_storage_mb' => 102400, 'is_active' => true, 'sort_order' => 3],
                ['name' => 'DIAMOND', 'slug' => 'diamond', 'description' => 'Maximum limits, dedicated resources, and priority cluster access.', 'monthly_price' => 19999, 'yearly_price' => 199990, 'max_users' => 0, 'max_storage_mb' => 512000, 'is_active' => true, 'sort_order' => 4],
            ];

            foreach ($defaultPlans as $dp) {
                try {
                    \App\Models\Central\Plan::on('central')->create($dp);
                } catch (\Throwable $e) {
                    try {
                        \App\Models\SubscriptionPlan::create($dp);
                    } catch (\Throwable $ex) {}
                }
            }

            try {
                $plans = \App\Models\Central\Plan::on('central')->standard()->orderBy('monthly_price', 'asc')->orderBy('id')->get();
            } catch (\Throwable $e) {
                $plans = collect();
            }
        }

        try {
            $companies = Company::on('central')->with('subscriptions.plan')->latest()->get();
        } catch (\Throwable $e) {
            $companies = collect();
        }

        if ($companies->isEmpty()) {
            try {
                $companies = \App\Models\Company::with('subscriptions.plan')->latest()->get();
            } catch (\Throwable $e) {
                $companies = collect();
            }
        }

        $currentCompanyDb = session('current_company_db');

        $planMonthlyRevenue = app(\App\Services\PlanMonthlyRevenue::class)->totals($companies);
        $catalogDistribution = app(\App\Services\SubscriptionDistribution::class)->currentCounts($companies);
        return view('superadmin.plans.index', compact('plans', 'companies', 'currentCompanyDb', 'planMonthlyRevenue', 'catalogDistribution'));
    }

    /**
     * Display Central Subscriptions Management Dashboard.
     */
    public function subscriptions(Request $request): View
    {
        try {
            $companies = Company::on('central')->with(['subscriptions.plan', 'companyModules'])->latest()->get();
        } catch (\Throwable $e) {
            $companies = collect();
        }

        if ($companies->isEmpty()) {
            try {
                $companies = \App\Models\Company::latest()->get();
            } catch (\Throwable $e) {
                $companies = collect();
            }
        }

        // Synchronize all company statuses in database before rendering subscriptions
        try {
            $subService = app(\App\Services\SubscriptionService::class);
            foreach ($companies as $comp) {
                $subService->syncCompanyStatus($comp);
            }
        } catch (\Throwable $e) {}

        try {
            $plans = \App\Models\Central\Plan::on('central')->standard()->with('modules')->orderBy('sort_order')->orderBy('monthly_price', 'asc')->get();
        } catch (\Throwable $e) {
            $plans = collect();
        }

        // Ensure 4 standard plans exist: FREE, GOLD, PLATINUM, DIAMOND
        if ($plans->isEmpty() || $plans->count() < 4) {
            $defaultPlans = [
                ['name' => 'FREE', 'slug' => 'free', 'description' => 'Essential features for small teams and trial accounts.', 'monthly_price' => 0, 'yearly_price' => 0, 'max_users' => 5, 'max_storage_mb' => 5120, 'is_active' => true, 'sort_order' => 1],
                ['name' => 'GOLD', 'slug' => 'gold', 'description' => 'Popular for growing businesses needing team collaboration.', 'monthly_price' => 4999, 'yearly_price' => 49990, 'max_users' => 25, 'max_storage_mb' => 25600, 'is_active' => true, 'sort_order' => 2],
                ['name' => 'PLATINUM', 'slug' => 'platinum', 'description' => 'Advanced capabilities for scaling enterprise organizations.', 'monthly_price' => 9999, 'yearly_price' => 99990, 'max_users' => 100, 'max_storage_mb' => 102400, 'is_active' => true, 'sort_order' => 3],
                ['name' => 'DIAMOND', 'slug' => 'diamond', 'description' => 'Maximum limits, dedicated resources, and priority cluster access.', 'monthly_price' => 19999, 'yearly_price' => 199990, 'max_users' => 0, 'max_storage_mb' => 512000, 'is_active' => true, 'sort_order' => 4],
            ];

            foreach ($defaultPlans as $dp) {
                try {
                    \App\Models\Central\Plan::on('central')->firstOrCreate(['slug' => $dp['slug']], $dp);
                } catch (\Throwable $e) {
                    try {
                        \App\Models\SubscriptionPlan::firstOrCreate(['slug' => $dp['slug']], $dp);
                    } catch (\Throwable $ex) {}
                }
            }

            try {
                $plans = \App\Models\Central\Plan::on('central')->standard()->with('modules')->orderBy('sort_order')->orderBy('monthly_price', 'asc')->get();
            } catch (\Throwable $e) {
                $plans = collect();
            }
        }

        // Use implemented Admin workspace features, including inactive modules.
        $modules = app(\App\Services\AdminWorkspaceModules::class)->all();

        // Fetch central audit logs for timeline
        try {
            $auditLogs = \App\Models\AuditLog::on('central')->latest()->take(25)->get();
        } catch (\Throwable $e) {
            $auditLogs = collect();
        }

        $currentCompanyDb = session('current_company_db');

        return view('superadmin.subscriptions.index', compact('companies', 'plans', 'modules', 'auditLogs', 'currentCompanyDb'));
    }
}
