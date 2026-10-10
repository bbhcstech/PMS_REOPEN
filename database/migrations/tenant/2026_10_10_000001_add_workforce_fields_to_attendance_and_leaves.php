<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        \App\Services\WorkforceRecordSchema::ensure();
    }

    public function down(): void
    {
        foreach (['attendances', 'leaves'] as $name) {
            foreach (['staff_category', 'staff_role_name', 'staff_designation', 'staff_designation_level'] as $column) {
                if (Schema::connection('tenant')->hasColumn($name, $column)) {
                    Schema::connection('tenant')->table($name, fn (Blueprint $table) => $table->dropColumn($column));
                }
            }
        }
    }
};
