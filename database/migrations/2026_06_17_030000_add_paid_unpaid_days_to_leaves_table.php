<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (! Schema::hasColumn('leaves', 'paid_days')) {
                $table->decimal('paid_days', 8, 2)->default(0)->after('total_days');
            }

            if (! Schema::hasColumn('leaves', 'unpaid_days')) {
                $table->decimal('unpaid_days', 8, 2)->default(0)->after('paid_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (Schema::hasColumn('leaves', 'unpaid_days')) {
                $table->dropColumn('unpaid_days');
            }

            if (Schema::hasColumn('leaves', 'paid_days')) {
                $table->dropColumn('paid_days');
            }
        });
    }
};
