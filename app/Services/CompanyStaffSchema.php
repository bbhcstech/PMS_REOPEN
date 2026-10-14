<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;

class CompanyStaffSchema
{
    public static function ensure(): void
    {
        $schema = (new User)->getConnection()->getSchemaBuilder();
        if (! $schema->hasTable('company_staff_roles')) {
            try {
                $schema->create('company_staff_roles', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('company_id')->index();
                    $table->string('name', 100);
                    $table->string('access_role', 20);
                    $table->timestamps();
                    $table->unique(['company_id', 'name']);
                });
            } catch (\Throwable $e) {
                if (! $schema->hasTable('company_staff_roles')) throw $e;
            }
        }
        if ($schema->hasTable('users') && ! $schema->hasColumn('users', 'company_staff_role_id')) {
            try {
                $schema->table('users', fn (Blueprint $table) => $table->unsignedBigInteger('company_staff_role_id')->nullable()->index());
            } catch (\Throwable $e) {
                if (! $schema->hasColumn('users', 'company_staff_role_id')) throw $e;
            }
        }
    }
}
