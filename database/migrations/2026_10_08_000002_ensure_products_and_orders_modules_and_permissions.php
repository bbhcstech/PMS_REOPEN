<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $modulesToEnsure = [
            [
                'name' => 'Products',
                'slug' => 'products',
                'icon' => 'bx-cube',
                'description' => 'Product and project portfolio tracking, categorizing home internal projects and client projects with deal cost metrics.',
                'route_name' => 'products.index',
                'route_prefix' => 'products',
                'is_core' => 1,
                'is_active' => 1,
                'sort_order' => 14,
            ],
            [
                'name' => 'Orders',
                'slug' => 'orders',
                'icon' => 'bx-cart',
                'description' => 'Client project orders, transaction value, itemized task billing, and payment status tracking.',
                'route_name' => 'orders.index',
                'route_prefix' => 'orders',
                'is_core' => 1,
                'is_active' => 1,
                'sort_order' => 15,
            ],
        ];

        // 1. Ensure central modules, plan entitlements, and company_modules exist
        try {
            if (Schema::connection('central')->hasTable('modules')) {
                foreach ($modulesToEnsure as $modData) {
                    $centralMod = DB::connection('central')->table('modules')->where('slug', $modData['slug'])->first();
                    if (! $centralMod) {
                        $centralModuleId = DB::connection('central')->table('modules')->insertGetId(array_merge($modData, [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]));
                    } else {
                        $centralModuleId = $centralMod->id;
                        DB::connection('central')->table('modules')->where('id', $centralModuleId)->update([
                            'is_active' => 1,
                            'updated_at' => now(),
                        ]);
                    }

                    // Attach to all subscription plans
                    if (Schema::connection('central')->hasTable('plans') && Schema::connection('central')->hasTable('plan_modules')) {
                        $plans = DB::connection('central')->table('plans')->get();
                        foreach ($plans as $plan) {
                            DB::connection('central')->table('plan_modules')->updateOrInsert(
                                ['plan_id' => $plan->id, 'module_id' => $centralModuleId],
                                ['created_at' => now(), 'updated_at' => now()]
                            );
                        }
                    }

                    // Enable for all companies in company_modules
                    if (Schema::connection('central')->hasTable('companies') && Schema::connection('central')->hasTable('company_modules')) {
                        $companies = DB::connection('central')->table('companies')->get();
                        foreach ($companies as $comp) {
                            DB::connection('central')->table('company_modules')->updateOrInsert(
                                ['company_id' => $comp->id, 'module_id' => $centralModuleId],
                                ['is_enabled' => 1, 'updated_at' => now()]
                            );
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Central DB operations gracefully pass if central connection is not configured
        }

        // 2. Ensure tenant / default modules table contains products and orders
        try {
            if (Schema::hasTable('modules')) {
                foreach ($modulesToEnsure as $modData) {
                    $tenantModule = DB::table('modules')->where('slug', $modData['slug'])->first();
                    if (! $tenantModule) {
                        $tenantModuleId = DB::table('modules')->insertGetId([
                            'name' => $modData['name'],
                            'slug' => $modData['slug'],
                            'icon' => $modData['icon'],
                            'description' => $modData['description'],
                            'route_prefix' => $modData['route_prefix'],
                            'is_core' => $modData['is_core'],
                            'is_active' => $modData['is_active'],
                            'sort_order' => $modData['sort_order'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $tenantModuleId = $tenantModule->id;
                        DB::table('modules')->where('id', $tenantModuleId)->update([
                            'is_active' => 1,
                            'updated_at' => now(),
                        ]);
                    }

                    // 3. Grant role_permissions for products and orders
                    if ($tenantModuleId && Schema::hasTable('role_permissions')) {
                        $allRoles = ['admin', 'superadmin', 'manager', 'hr', 'employee'];
                        $existingRoles = DB::table('role_permissions')->distinct()->pluck('role')->toArray();
                        $rolesToSeed = array_unique(array_merge($allRoles, $existingRoles));

                        foreach ($rolesToSeed as $role) {
                            $isFullManager = in_array(strtolower($role), ['admin', 'superadmin', 'manager', 'hr'], true);

                            DB::table('role_permissions')->updateOrInsert(
                                ['role' => $role, 'module_id' => $tenantModuleId],
                                [
                                    'can_view' => 1,
                                    'can_create' => $isFullManager ? 1 : 0,
                                    'can_edit' => $isFullManager ? 1 : 0,
                                    'can_delete' => $isFullManager ? 1 : 0,
                                    'can_approve' => $isFullManager ? 1 : 0,
                                    'can_export' => 1,
                                    'can_assign' => $isFullManager ? 1 : 0,
                                    'updated_at' => now(),
                                ]
                            );
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Gracefully pass on tenant module seeding error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Preserve modules and permissions on rollback
    }
};
