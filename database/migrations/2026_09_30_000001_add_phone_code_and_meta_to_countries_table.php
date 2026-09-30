<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Support\CountryPhone;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('countries')) {
            Schema::table('countries', function (Blueprint $table) {
                if (!Schema::hasColumn('countries', 'phone_code')) {
                    $table->string('phone_code', 10)->nullable()->after('name');
                }
                if (!Schema::hasColumn('countries', 'iso_code')) {
                    $table->string('iso_code', 10)->nullable()->after('phone_code');
                }
                if (!Schema::hasColumn('countries', 'min_digits')) {
                    $table->integer('min_digits')->default(10)->after('iso_code');
                }
                if (!Schema::hasColumn('countries', 'max_digits')) {
                    $table->integer('max_digits')->default(10)->after('min_digits');
                }
                if (!Schema::hasColumn('countries', 'flag_url')) {
                    $table->string('flag_url', 255)->nullable()->after('max_digits');
                }
            });

            // Populate existing records with phone_code, iso_code, min/max digits, and flag_url
            $map = CountryPhone::map();
            foreach ($map as $countryName => $meta) {
                $flagUrl = 'https://flagcdn.com/w20/' . strtolower($meta['iso'] ?? 'in') . '.png';
                DB::table('countries')
                    ->where('name', $countryName)
                    ->update([
                        'phone_code' => $meta['dial_code'] ?? '+91',
                        'iso_code'   => strtoupper($meta['iso'] ?? 'IN'),
                        'min_digits' => (int) ($meta['min_digits'] ?? 10),
                        'max_digits' => (int) ($meta['max_digits'] ?? 10),
                        'flag_url'   => $flagUrl,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('countries')) {
            Schema::table('countries', function (Blueprint $table) {
                $columnsToDrop = [];
                foreach (['phone_code', 'iso_code', 'min_digits', 'max_digits', 'flag_url'] as $col) {
                    if (Schema::hasColumn('countries', $col)) {
                        $columnsToDrop[] = $col;
                    }
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
