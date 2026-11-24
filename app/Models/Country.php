<?php
namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class Country extends TenantModel
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone_code',
        'iso_code',
        'min_digits',
        'max_digits',
        'flag_url',
    ];

    /**
     * Alias for phone_code (dial_code)
     */
    public function getDialCodeAttribute(): ?string
    {
        return $this->phone_code;
    }

    /**
     * Booted hook to ensure countries are seeded if table is empty
     */
    protected static function booted(): void
    {
        static::ensureAllCountriesSeeded();
    }

    /**
     * Ensure countries table has all country codes seeded.
     */
    public static function ensureAllCountriesSeeded(): void
    {
        try {
            $model = new static;
            $conn = $model->getConnectionName() ?: 'tenant';

            if (! \Illuminate\Support\Facades\Schema::connection($conn)->hasTable('countries')) {
                return;
            }

            if (\Illuminate\Support\Facades\DB::connection($conn)->table('countries')->count() >= 100) {
                return;
            }

            $map = \App\Support\CountryPhone::map();
            foreach ($map as $name => $meta) {
                $flagUrl = 'https://flagcdn.com/w20/' . strtolower($meta['iso'] ?? 'in') . '.png';
                \Illuminate\Support\Facades\DB::connection($conn)->table('countries')->updateOrInsert(
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
            \Illuminate\Support\Facades\Log::warning('Country::ensureAllCountriesSeeded error: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve all countries ordered by name.
     */
    public static function getAllWithPhoneCodes()
    {
        static::ensureAllCountriesSeeded();
        return static::orderBy('name')->get();
    }
}