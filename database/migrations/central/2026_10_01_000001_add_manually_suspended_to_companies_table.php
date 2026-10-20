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
            if (! Schema::connection('central')->hasColumn('companies', 'manually_suspended')) {
                $table->boolean('manually_suspended')->default(false)->after('suspended_at')
                    ->comment('True when explicitly suspended by Super Admin (not by subscription expiry). Renewal/plan activation must NOT lift this flag.');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('companies', function (Blueprint $table) {
            $table->dropColumn('manually_suspended');
        });
    }
};
