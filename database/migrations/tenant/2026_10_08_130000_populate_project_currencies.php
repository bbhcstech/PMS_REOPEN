<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $currencies = [
            ['INR', 'Indian Rupee', '₹'],
            ['USD', 'US Dollar', '$'],
            ['EUR', 'Euro', '€'],
            ['GBP', 'British Pound', '£'],
            ['AED', 'UAE Dirham', 'د.إ'],
            ['AUD', 'Australian Dollar', 'A$'],
            ['CAD', 'Canadian Dollar', 'C$'],
            ['SGD', 'Singapore Dollar', 'S$'],
            ['JPY', 'Japanese Yen', '¥'],
        ];

        foreach ($currencies as [$code, $name, $symbol]) {
            $table = DB::connection('tenant')->table('currencies');
            if (!$table->whereRaw('UPPER(currency_code) = ?', [$code])->exists()) {
                DB::connection('tenant')->table('currencies')->insert([
                    'currency_code' => $code,
                    'currency_name' => $name,
                    'currency_symbol' => $symbol,
                    'no_of_decimal' => $code === 'JPY' ? 0 : 2,
                    'thousand_separator' => ',',
                    'decimal_separator' => '.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Currency IDs may be referenced by projects; preserve those records.
    }
};
