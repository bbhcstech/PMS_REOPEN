<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Central\Plan;
use App\Models\SubscriptionPlan;
use App\Services\ProvisioningPlan;
use App\Support\SupportedPlans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

config(['database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('central');
$schema = Schema::connection('central');
$schema->create('plans', function ($t) {
    $t->id(); $t->string('slug')->unique(); $t->string('name');
    $t->decimal('monthly_price')->default(0); $t->decimal('yearly_price')->default(0);
    $t->integer('max_users')->default(0); $t->integer('max_storage_mb')->default(0);
    $t->boolean('is_active')->default(true); $t->integer('sort_order')->default(0); $t->timestamps();
});
$schema->create('modules', function ($t) {
    $t->id(); foreach (['name', 'slug', 'icon', 'description', 'route_prefix'] as $field) $t->string($field);
    $t->boolean('is_core'); $t->boolean('is_active'); $t->integer('sort_order'); $t->timestamps();
});
$schema->create('plan_modules', function ($t) {
    $t->unsignedBigInteger('plan_id'); $t->unsignedBigInteger('module_id'); $t->timestamps();
    $t->unique(['plan_id', 'module_id']);
});
$schema->create('company_subscriptions', function ($t) { $t->id(); $t->unsignedBigInteger('plan_id'); });
function catalogCheck($ok, $message) { if (! $ok) throw new RuntimeException($message); }
foreach (SupportedPlans::defaults() as $slug => $values) Plan::create($values + ['slug' => $slug]);
$gold = Plan::where('slug', 'gold')->firstOrFail();
$gold->update(['monthly_price' => 5678]);
foreach (['starter', 'professional', 'enterprise'] as $slug) Plan::create(['slug' => $slug, 'name' => strtoupper($slug)]);
$legacy = Plan::where('slug', 'starter')->firstOrFail();
$subscriptionId = DB::connection('central')->table('company_subscriptions')->insertGetId(['plan_id' => $legacy->id]);
$migration = require dirname(__DIR__, 2) . '/database/migrations/central/2026_10_09_120000_retire_nonstandard_subscription_plans.php';
$migration->up(); $migration->up();
$seeder = new Database\Seeders\SubscriptionCatalogSeeder;
$seeder->run(); $seeder->run();
foreach ([Plan::class, SubscriptionPlan::class] as $model) {
    $slugs = $model::standard()->orderBy('id')->pluck('slug')->all();
    catalogCheck($slugs === SupportedPlans::SLUGS, 'Catalog must contain exactly the four supported plans.');
}
catalogCheck(Plan::count() === 7, 'Historical plans must remain intact without new duplicates.');
catalogCheck(Plan::whereNotIn('slug', SupportedPlans::SLUGS)->where('is_active', true)->count() === 0, 'Obsolete plans must be retired.');
catalogCheck($gold->fresh()->monthly_price == 5678, 'Configured pricing was overwritten.');
$subscription = App\Models\Central\Subscription::findOrFail($subscriptionId);
catalogCheck($subscription->plan->id === $legacy->id, 'Existing subscription history was lost.');
foreach ([$legacy->slug, $legacy->id] as $selection) {
    try {
        (new ProvisioningPlan)->resolve(Request::create('/', 'POST', ['subscription_plan' => $selection]));
        throw new RuntimeException('An obsolete plan was selectable for a new company.');
    } catch (ValidationException $e) {}
}
catalogCheck((new ProvisioningPlan)->resolve(Request::create('/', 'POST', ['subscription_plan' => 'Gold']))->id === $gold->id, 'Supported provisioning changed.');
echo "PASS: four-plan catalog, retirement, repeat seeding, configured prices, historical subscriptions and provisioning validation.\n";
