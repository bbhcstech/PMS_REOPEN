<?php

use App\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company databases were created with countries(id, name) only. Without the phone
     * columns the country list could not be seeded, leaving the employee form's Country
     * and mobile country-code pickers empty. Add the columns and seed every country.
     */
    public function up(): void
    {
        if (! Schema::connection('tenant')->hasTable('countries')) {
            Schema::connection('tenant')->create('countries', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 191)->unique();
            });
        }

        Country::ensureCountryColumns('tenant');
        Country::ensureAllCountriesSeeded();
    }

    public function down(): void
    {
        // Country rows are reference data used by employee and client records; keep them.
    }
};
