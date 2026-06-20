<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SuperAdminSeeder::class);
        $this->call(SubscriptionCatalogSeeder::class);
        $this->call(EnterpriseCompanySeeder::class);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);



        //  CALL SETTINGS SEEDER
        $this->call([
            ProfileSettingSeeder::class,
            RoleManagementSeeder::class,
            PayrollModuleSeeder::class,
        ]);
    }
}
