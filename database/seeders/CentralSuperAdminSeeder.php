<?php

namespace Database\Seeders;

use App\Models\Central\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CentralSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        SuperAdmin::updateOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@bbhpms.com')],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'SuperAdmin@123')),
                'is_active' => true,
            ]
        );
    }
}
