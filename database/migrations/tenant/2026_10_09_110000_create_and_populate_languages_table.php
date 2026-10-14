<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        if (! Schema::connection('tenant')->hasTable('languages')) {
            Schema::connection('tenant')->create('languages', function (Blueprint $table) {
                $table->id();
                $table->string('code', 3)->unique();
                $table->string('iso_639_3', 3)->unique();
                $table->string('name')->index();
                $table->string('scope', 1);
                $table->string('type', 1);
                $table->string('flag_url')->nullable();
            });
        }
        DB::connection('tenant')->transaction(fn () => \App\Support\LanguageCatalog::populate(DB::connection('tenant')));
    }

    public function down(): void
    {
        // Keep reference data and saved client codes intact on rollback.
    }
};
