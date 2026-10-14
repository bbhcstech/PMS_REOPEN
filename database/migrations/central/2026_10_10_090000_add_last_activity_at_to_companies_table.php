<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->table('companies', function (Blueprint $table) {
            if (! Schema::connection('central')->hasColumn('companies', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->index()
                    ->comment('Last request made by any user signed in to this company workspace.');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('companies', function (Blueprint $table) {
            $table->dropColumn('last_activity_at');
        });
    }
};
