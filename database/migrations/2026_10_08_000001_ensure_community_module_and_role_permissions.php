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
        // 1. Ensure community tables exist in tenant / default connection
        if (! Schema::hasTable('community_messages')) {
            Schema::create('community_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->text('message')->nullable();
                $table->string('attachment_path')->nullable();
                $table->string('attachment_name')->nullable();
                $table->string('attachment_type')->nullable();
                $table->unsignedBigInteger('attachment_size')->nullable();
                $table->boolean('is_pinned')->default(false)->index();
                $table->unsignedBigInteger('pinned_by')->nullable();
                $table->timestamp('pinned_at')->nullable();
                $table->timestamp('edited_at')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('parent_id')->references('id')->on('community_messages')->onDelete('set null');
            });
        }

        if (! Schema::hasTable('community_reactions')) {
            Schema::create('community_reactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('message_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('emoji', 32);
                $table->timestamps();

                $table->unique(['message_id', 'user_id', 'emoji'], 'community_reactions_unique');
                $table->foreign('message_id')->references('id')->on('community_messages')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('community_user_states')) {
            Schema::create('community_user_states', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('last_read_message_id')->nullable();
                $table->timestamp('last_read_at')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'user_id'], 'community_user_states_unique');
            });
        }

        // 2. Ensure central module & plan entitlements exist
        try {
            if (Schema::connection('central')->hasTable('modules')) {
                $centralModule = DB::connection('central')->table('modules')->where('slug', 'community')->first();
                if (! $centralModule) {
                    $centralModuleId = DB::connection('central')->table('modules')->insertGetId([
                        'name' => 'Community',
                        'slug' => 'community',
                        'icon' => 'bx-chat',
                        'description' => 'Company-wide group messaging and announcements channel.',
                        'route_name' => 'community.index',
                        'route_prefix' => 'community',
                        'is_core' => 1,
                        'is_active' => 1,
                        'sort_order' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $centralModuleId = $centralModule->id;
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
        } catch (\Throwable $e) {
            // Central DB operations gracefully pass if central connection is not configured
        }

        // 3. Ensure tenant modules table contains community
        $tenantModuleId = null;
        if (Schema::hasTable('modules')) {
            $tMod = DB::table('modules')->where('slug', 'community')->first();
            if (! $tMod) {
                $tenantModuleId = DB::table('modules')->insertGetId([
                    'name' => 'Community',
                    'slug' => 'community',
                    'icon' => 'bx-chat',
                    'description' => 'Company-wide group messaging and announcements channel.',
                    'route_name' => 'community.index',
                    'route_prefix' => 'community',
                    'is_core' => 1,
                    'is_active' => 1,
                    'sort_order' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $tenantModuleId = $tMod->id;
                DB::table('modules')->where('id', $tenantModuleId)->update([
                    'is_active' => 1,
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Grant role_permissions for Community to all roles
        if ($tenantModuleId && Schema::hasTable('role_permissions')) {
            $allRoles = ['admin', 'manager', 'hr', 'employee'];
            $existingRoles = DB::table('role_permissions')->distinct()->pluck('role')->toArray();
            $rolesToSeed = array_unique(array_merge($allRoles, $existingRoles));

            foreach ($rolesToSeed as $role) {
                $isFullManager = in_array(strtolower($role), ['admin', 'superadmin', 'manager', 'hr'], true);

                DB::table('role_permissions')->updateOrInsert(
                    ['role' => $role, 'module_id' => $tenantModuleId],
                    [
                        'can_view' => 1,
                        'can_create' => 1,
                        'can_edit' => 1,
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Preserving community tables and permissions on rollback
    }
};
