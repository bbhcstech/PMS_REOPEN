<?php

use App\Services\AdminWorkspaceModules;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::connection('central')->hasTable('modules')) {
            app(AdminWorkspaceModules::class)->syncSidebarRegistry();
        }
    }

    public function down(): void
    {
        // Preserve module IDs referenced by company entitlements and permissions.
    }
};
