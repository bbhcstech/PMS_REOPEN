<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

class SubscriptionChangeSchema
{
    public static function ensure(): void
    {
        $schema = Schema::connection('central');
        foreach ([
            'companies' => '2026_08_26_000001_add_subscription_lifecycle_fields_to_companies_table.php',
            'company_subscriptions' => '2026_08_26_000002_add_lifecycle_fields_to_central_company_subscriptions_table.php',
        ] as $table => $file) {
            if ($schema->hasTable($table)) {
                (require database_path('migrations/central/' . $file))->up();
            }
        }
        foreach ([
            'modules' => '2026_08_08_000009_create_central_modules_table.php',
            'plan_modules' => '2026_08_08_000010_create_central_plan_modules_table.php',
            'company_modules' => '2026_08_08_000011_create_central_company_modules_table.php',
            'subscription_histories' => '2026_08_26_000003_create_central_subscription_histories_table.php',
        ] as $table => $file) {
            if (! $schema->hasTable($table)) {
                try { (require database_path('migrations/central/' . $file))->up(); }
                catch (\Throwable $e) { if (! $schema->hasTable($table)) throw $e; }
            }
        }
    }
}
