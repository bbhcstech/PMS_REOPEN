<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'superadmin@bbhpms.com')],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(env('SUPERADMIN_PASSWORD', 'SuperAdmin@123')),
                'role' => 'superadmin',
                'email_verified_at' => now(),
                'login_allowed' => true,
                'email_notifications' => true,
            ]
        );
    }
}
