<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Support\CountryPhone;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $map = CountryPhone::map();
        $connections = ['mysql', 'tenant'];
        if (config('database.connections.central')) {
            $connections[] = 'central';
        }

        foreach (array_unique($connections) as $conn) {
            try {
                if (! \Illuminate\Support\Facades\Schema::connection($conn)->hasTable('countries')) {
                    continue;
                }

                foreach ($map as $name => $meta) {
                    $flagUrl = 'https://flagcdn.com/w20/' . strtolower($meta['iso'] ?? 'in') . '.png';

                    DB::connection($conn)->table('countries')->updateOrInsert(
                        ['name' => $name],
                        [
                            'phone_code' => $meta['dial_code'] ?? '+91',
                            'iso_code'   => strtoupper($meta['iso'] ?? 'IN'),
                            'min_digits' => (int) ($meta['min_digits'] ?? 10),
                            'max_digits' => (int) ($meta['max_digits'] ?? 10),
                            'flag_url'   => $flagUrl,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // Ignore connection error if db does not exist
            }
        }
    }
}
