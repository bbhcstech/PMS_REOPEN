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
        (require database_path('migrations/tenant/2026_10_11_000001_add_attendance_session_timezone.php'))->up();
        (require database_path('migrations/tenant/2026_10_11_000002_add_clock_out_photo_to_attendances.php'))->up();
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
