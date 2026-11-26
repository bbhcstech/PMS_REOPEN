<?php

/**
 * One-time repair for the corrupted pms_central.plan_modules table (MariaDB page 0 corruption that
 * crashes the server whenever `modules` / `plan_modules` is read, e.g. the feature check after login).
 *
 * PRECONDITIONS (done by a person, with MySQL stopped):
 *   1. Backup exists: D:\xampp\mysql\data_backup_20261014_before_plan_modules_repair
 *   2. D:\xampp\mysql\data\pms_central\plan_modules.ibd moved to D:\xampp\mysql\quarantine_20261014\
 *   3. MySQL started again.
 *
 * Run: php scripts/repair_plan_modules.php            (dry run: shows what would be rebuilt)
 *      php scripts/repair_plan_modules.php --apply    (drops the broken table entry, recreates and refills it)
 *
 * Refill rule (same as SubscriptionService / Super Admin plan toggles):
 *   - paid tiers (gold, platinum, diamond) include every module;
 *   - other plans include the module slugs stored in their own `plans.features` list.
 */

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$apply = in_array('--apply', $argv, true);
$central = DB::connection('central');
$database = $central->getDatabaseName();

$ibd = rtrim((string) $central->selectOne('SELECT @@datadir AS d')->d, '\\/') . DIRECTORY_SEPARATOR . $database . DIRECTORY_SEPARATOR . 'plan_modules.ibd';
if (is_file($ibd)) {
    fwrite(STDERR, "Refusing to run: the corrupted file is still in place ({$ibd}). Move it out with MySQL stopped first.\n");
    exit(1);
}

$modules = $central->table('modules')->pluck('id', 'slug');            // must no longer crash
$plans = $central->table('plans')->get(['id', 'slug', 'features']);
echo "modules readable: {$modules->count()} rows; plans: {$plans->count()}\n";

$rows = [];
foreach ($plans as $plan) {
    $slug = strtolower((string) $plan->slug);
    if (in_array($slug, ['gold', 'platinum', 'diamond'], true)) {
        $ids = $modules->values()->all();
    } else {
        $features = json_decode((string) $plan->features, true) ?: [];
        $ids = $modules->only(array_filter($features, 'is_string'))->values()->all();
    }
    echo str_pad($slug ?: ('plan#' . $plan->id), 12) . ' -> ' . count($ids) . " module(s)\n";
    foreach ($ids as $id) $rows[] = ['plan_id' => $plan->id, 'module_id' => $id, 'created_at' => now(), 'updated_at' => now()];
}

if (! $apply) {
    echo "\nDry run only. Re-run with --apply to rebuild plan_modules (" . count($rows) . " rows).\n";
    exit(0);
}

// The .frm still exists while the tablespace is gone: DROP removes the broken dictionary entry.
$central->statement('SET FOREIGN_KEY_CHECKS=0');
$central->statement('DROP TABLE IF EXISTS `plan_modules`');
$central->statement('SET FOREIGN_KEY_CHECKS=1');
(require dirname(__DIR__) . '/database/migrations/central/2026_08_08_000010_create_central_plan_modules_table.php')->up();
foreach (array_chunk($rows, 500) as $chunk) $central->table('plan_modules')->insert($chunk);

// Verify the queries that used to crash the server.
$count = $central->table('plan_modules')->count();
$central->table('company_modules')->join('modules', 'modules.id', '=', 'company_modules.module_id')->select('company_modules.is_enabled')->limit(1)->get();
$central->table('modules')->orderBy('sort_order')->orderBy('name')->limit(1)->get();
echo "\nRepaired: plan_modules rebuilt with {$count} rows; feature-check queries run without crashing.\n";
