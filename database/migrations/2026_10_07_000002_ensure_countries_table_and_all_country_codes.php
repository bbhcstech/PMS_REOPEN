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
        $connections = ['mysql', 'tenant'];
        if (config('database.connections.central')) {
            $connections[] = 'central';
        }

        $map = CountryPhone::map();

        foreach (array_unique($connections) as $connection) {
            try {
                if (! Schema::connection($connection)->hasTable('countries')) {
                    Schema::connection($connection)->create('countries', function (Blueprint $table) {
                        $table->bigIncrements('id');
                        $table->string('name', 191)->unique();
                        $table->string('phone_code', 15)->nullable();
                        $table->string('iso_code', 10)->nullable();
                        $table->integer('min_digits')->default(10);
                        $table->integer('max_digits')->default(10);
                        $table->string('flag_url', 255)->nullable();
                    });
                } else {
                    Schema::connection($connection)->table('countries', function (Blueprint $table) use ($connection) {
                        if (! Schema::connection($connection)->hasColumn('countries', 'phone_code')) {
                            $table->string('phone_code', 15)->nullable()->after('name');
                        }
                        if (! Schema::connection($connection)->hasColumn('countries', 'iso_code')) {
                            $table->string('iso_code', 10)->nullable()->after('phone_code');
                        }
                        if (! Schema::connection($connection)->hasColumn('countries', 'min_digits')) {
                            $table->integer('min_digits')->default(10)->after('iso_code');
                        }
                        if (! Schema::connection($connection)->hasColumn('countries', 'max_digits')) {
                            $table->integer('max_digits')->default(10)->after('min_digits');
                        }
                        if (! Schema::connection($connection)->hasColumn('countries', 'flag_url')) {
                            $table->string('flag_url', 255)->nullable()->after('max_digits');
                        }
                    });
                }

                foreach ($map as $name => $meta) {
                    $flagUrl = 'https://flagcdn.com/w20/' . strtolower($meta['iso'] ?? 'in') . '.png';
                    DB::connection($connection)->table('countries')->updateOrInsert(
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
                \Illuminate\Support\Facades\Log::warning("Migration countries setup failed on {$connection}: " . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe keep table
    }
};
