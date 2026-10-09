<?php

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void { \App\Services\CompanyStaffSchema::ensure(); }
    public function down(): void { /* Preserve company roles and account assignments. */ }
};
