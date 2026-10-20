<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            if (! Schema::connection('tenant')->hasTable('modules')) {
                return;
            }

            if (! Schema::connection('central')->hasTable('modules')) {
                return;
            }

            $centralModules = DB::connection('central')->table('modules')->get();
            if ($centralModules->isEmpty()) {
                return;
            }

            $tenantModules = DB::connection('tenant')->table('modules')->get();
            $tenantModuleIds = $tenantModules->pluck('id')->all();
            $tenantModuleSlugs = $tenantModules->pluck('id', 'slug')->all();

            $missingModules = $centralModules->whereNotIn('id', $tenantModuleIds);

            if ($missingModules->isNotEmpty()) {
                DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS = 0;');

                foreach ($missingModules as $cMod) {
                    if (isset($tenantModuleSlugs[$cMod->slug])) {
                        $oldId = $tenantModuleSlugs[$cMod->slug];
                        DB::connection('tenant')->table('modules')->where('id', $oldId)->update(['id' => $cMod->id]);
                        if (Schema::connection('tenant')->hasTable('role_permissions')) {
                            DB::connection('tenant')->table('role_permissions')->where('module_id', $oldId)->update(['module_id' => $cMod->id]);
                        }
                        DB::connection('tenant')->table('modules')->where('parent_id', $oldId)->update(['parent_id' => $cMod->id]);
                    } else {
                        DB::connection('tenant')->table('modules')->insert([
                            'id'           => $cMod->id,
                            'name'         => $cMod->name,
                            'slug'         => $cMod->slug,
                            'icon'         => $cMod->icon,
                            'description'  => $cMod->description,
                            'route_prefix' => $cMod->route_prefix,
                            'route_name'   => $cMod->route_name,
                            'parent_id'    => $cMod->parent_id,
                            'is_core'      => $cMod->is_core,
                            'is_active'    => $cMod->is_active,
                            'sort_order'   => $cMod->sort_order,
                            'created_at'   => $cMod->created_at ?: now(),
                            'updated_at'   => $cMod->updated_at ?: now(),
                        ]);
                    }
                }

                DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS = 1;');
            }
        } catch (\Throwable $e) {
            // Ignore in environments where central connection or database is not reachable during migration
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No teardown needed
    }
};
