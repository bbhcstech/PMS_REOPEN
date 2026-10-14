<?php

use App\Models\Central\Plan;
use App\Support\SupportedPlans;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        if (! Schema::connection('central')->hasTable('plans')) return;
        DB::connection('central')->transaction(function () {
            Plan::whereNotIn('slug', SupportedPlans::SLUGS)->update(['is_active' => false]);
            foreach (SupportedPlans::defaults() as $slug => $values) {
                Plan::firstOrCreate(['slug' => $slug], $values + ['is_active' => true, 'sort_order' => array_search($slug, SupportedPlans::SLUGS, true) + 1]);
            }
        });
    }

    public function down(): void
    {
        // Do not reactivate obsolete offerings or remove subscribed plan records.
    }
};
