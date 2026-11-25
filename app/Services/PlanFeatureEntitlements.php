<?php

namespace App\Services;

use Illuminate\Support\Facades\{DB, Schema};

/** Shared plan access for the matrix, company defaults, navigation and route checks. */
class PlanFeatureEntitlements
{
    private array $rows = [];

    private function rows($plan): array
    {
        $id = (int) $plan->id;
        if (!array_key_exists($id, $this->rows)) {
            $explicit = Schema::connection('central')->hasColumn('plan_modules', 'is_enabled');
            $rows = DB::connection('central')->table('plan_modules')
                ->join('modules', 'modules.id', '=', 'plan_modules.module_id')
                ->where('plan_id', $id)->get($explicit ? ['modules.slug', 'plan_modules.is_enabled'] : ['modules.slug']);
            $this->rows[$id] = $rows->groupBy('slug')->map(fn ($group) =>
                !$explicit || $group->every(fn ($row) => (bool) $row->is_enabled))->all();
        }
        return $this->rows[$id];
    }

    public function allows($plan, string $slug): bool
    {
        if (!$plan) return false;
        $rows = $this->rows($plan);
        $features = app(FeatureEntitlements::class);
        $planSlugs = $features->planSlugs($slug);
        $grant = false;
        foreach ($features->ancestry($slug) as $feature) {
            foreach (array_merge([$feature], $features->aliasesOf($feature)) as $candidate) {
                if (!array_key_exists($candidate, $rows)) continue;
                if (!$rows[$candidate]) return false;
                if (in_array($candidate, $planSlugs, true)) $grant = true;
            }
        }
        foreach ($planSlugs as $candidate) {
            if (($rows[$candidate] ?? false) === true) $grant = true;
        }
        if ($rows) return $grant;

        // Legacy plans with no configured list retain their established tier defaults.
        $tier = strtolower((string) $plan->slug);
        if (in_array($tier, ['gold', 'platinum', 'diamond'], true)) return true;
        if (in_array($tier, ['free', 'trial', 'starter'], true)) {
            $defaults = [
                'dashboard', 'notifications', 'organization', 'hr', 'employees', 'attendance',
                'leaves', 'holidays', 'events', 'my-documents', 'community', 'tickets', 'settings', 'products', 'orders',
            ];
            return count(array_intersect($planSlugs, $defaults)) > 0;
        }
        return false;
    }

    public function forget(?int $planId = null): void
    {
        if ($planId === null) $this->rows = [];
        else unset($this->rows[$planId]);
    }

    public function set($plan, $module, bool $enabled): void
    {
        (require database_path('migrations/central/2026_10_15_120000_add_enabled_to_plan_modules.php'))->up();
        DB::connection('central')->transaction(function () use ($plan, $module, $enabled) {
            DB::connection('central')->table('plans')->where('id', $plan->id)->lockForUpdate()->first();
            $this->forget((int) $plan->id);
            if (!$this->rows($plan)) {
                // First edit snapshots existing defaults, so toggling one feature never changes siblings.
                $modules = \App\Models\Module::on('central')->get();
                $defaults = $modules->filter(fn ($item) => $this->allows($plan, $item->slug))->map(fn ($item) => [
                    'plan_id' => $plan->id, 'module_id' => $item->id,
                    'is_enabled' => true, 'created_at' => now(), 'updated_at' => now(),
                ])->values()->all();
                if ($defaults) DB::connection('central')->table('plan_modules')->insert($defaults);
            }
            DB::connection('central')->table('plan_modules')->updateOrInsert(
                ['plan_id' => $plan->id, 'module_id' => $module->id],
                ['is_enabled' => $enabled, 'updated_at' => now()]
            );
            $features = app(FeatureEntitlements::class);
            if (!$features->isLegacyAlias($module->slug)) {
                DB::connection('central')->table('plan_modules')->where('plan_id', $plan->id)
                    ->whereIn('module_id', DB::connection('central')->table('modules')->whereIn('slug', $features->aliasesOf($module->slug))->pluck('id'))->delete();
            }
            $this->forget((int) $plan->id);
            $plan->features = \App\Models\Module::on('central')->get()->filter(fn ($item) => $this->allows($plan, $item->slug))->pluck('slug')->values()->all();
            $plan->save();
        });
        $this->forget((int) $plan->id);
    }
}
