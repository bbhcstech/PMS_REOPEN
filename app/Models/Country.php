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

            // Each company has its own database: remember per database, not per connection name.
            static $checked = [];
            $checkKey = $conn . '|' . (string) config("database.connections.{$conn}.database");
            if (isset($checked[$checkKey])) {
                return;
            }

            if (! \Illuminate\Support\Facades\Schema::connection($conn)->hasTable('countries')) {
                \Illuminate\Support\Facades\Schema::connection($conn)->create('countries', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->string('name', 191)->unique();
                });
            }

            // Older company databases were created with only id + name, which made the
            // seeding below fail silently and left the Country / dial-code pickers empty.
            static::ensureCountryColumns($conn);

            $table = \Illuminate\Support\Facades\DB::connection($conn)->table('countries');
            if ((clone $table)->count() >= 100 && ! (clone $table)->whereNull('phone_code')->exists()) {
                $checked[$checkKey] = true;
                return;
            }

            $map = \App\Support\CountryPhone::map();
            foreach ($map as $name => $meta) {
                $flagUrl = 'https://flagcdn.com/w20/' . strtolower($meta['iso'] ?? 'in') . '.png';
                $metadata = [
                        'phone_code' => $meta['dial_code'] ?? '+91',
                        'iso_code'   => strtoupper($meta['iso'] ?? 'IN'),
                        'min_digits' => (int) ($meta['min_digits'] ?? 10),
                        'max_digits' => (int) ($meta['max_digits'] ?? 10),
                        'flag_url'   => $flagUrl,
                    ];
                $existing = \Illuminate\Support\Facades\DB::connection($conn)->table('countries')->where('name', $name);
                if (!(clone $existing)->exists()) {
                    \Illuminate\Support\Facades\DB::connection($conn)->table('countries')->insert(['name' => $name] + $metadata);
                } elseif (!(clone $existing)->value('phone_code')) {
                    $existing->update($metadata);
                }
            }

            $checked[$checkKey] = true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Country::ensureAllCountriesSeeded error: ' . $e->getMessage());
        }
    }

    /**
     * Add the phone metadata columns to a countries table that is missing them.
     */
    public static function ensureCountryColumns(string $conn): void
    {
        $schema = \Illuminate\Support\Facades\Schema::connection($conn);
        $columns = [
            'phone_code' => fn ($t) => $t->string('phone_code', 15)->nullable(),
            'iso_code'   => fn ($t) => $t->string('iso_code', 10)->nullable(),
            'min_digits' => fn ($t) => $t->integer('min_digits')->default(10),
            'max_digits' => fn ($t) => $t->integer('max_digits')->default(10),
            'flag_url'   => fn ($t) => $t->string('flag_url', 255)->nullable(),
        ];

        $missing = array_filter($columns, fn ($column) => ! $schema->hasColumn('countries', $column), ARRAY_FILTER_USE_KEY);
        if (empty($missing)) {
            return;
        }

        $schema->table('countries', function (\Illuminate\Database\Schema\Blueprint $table) use ($missing) {
            foreach ($missing as $define) {
                $define($table);
            }
        });
    }

    /**
     * Countries for Country / dial-code pickers. Never empty: if the company database
     * has no usable country rows (not migrated yet, or seeding failed), fall back to the
     * built-in country list so forms always show countries and phone codes.
     */
    public static function forForms()
    {
        try {
            static::ensureAllCountriesSeeded();
            $countries = static::orderBy('name')->get();
            if ($countries->isNotEmpty()) {
                return $countries;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Country::forForms fallback: ' . $e->getMessage());
        }

        return collect(\App\Support\CountryPhone::map())
            ->map(function ($meta, $name) {
                $country = new static();
                $country->forceFill([
                    'name'       => $name,
                    'phone_code' => $meta['dial_code'] ?? null,
                    'iso_code'   => strtoupper($meta['iso'] ?? ''),
                    'min_digits' => (int) ($meta['min_digits'] ?? 10),
                    'max_digits' => (int) ($meta['max_digits'] ?? 10),
                    'flag_url'   => 'https://flagcdn.com/w20/' . strtolower($meta['iso'] ?? 'in') . '.png',
                ]);

                return $country;
            })
            ->sortBy('name')
            ->values();
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
