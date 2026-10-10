<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WorkforceRecordSchema
{
    public static function ensure(): void
    {
        CompanyStaffSchema::ensure();
        $schema = Schema::connection('tenant');
        foreach (['attendances', 'leaves'] as $name) {
            if (! $schema->hasTable($name)) continue;
            foreach (['staff_category', 'staff_role_name', 'staff_designation', 'staff_designation_level'] as $column) {
                if ($schema->hasColumn($name, $column)) continue;
                try {
                    $schema->table($name, function (Blueprint $table) use ($column) {
                        if ($column === 'staff_designation_level') $table->unsignedInteger($column)->nullable();
                        else $table->string($column)->nullable();
                    });
                } catch (\Throwable $e) {
                    if (! $schema->hasColumn($name, $column)) throw $e;
                }
            }
        }
    }
}
