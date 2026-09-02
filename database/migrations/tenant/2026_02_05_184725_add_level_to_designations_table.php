<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('designations') && ! Schema::hasColumn('designations', 'level')) {
            Schema::table('designations', function (Blueprint $table) {
                if (Schema::hasColumn('designations', 'unique_code')) {
                    $table->integer('level')->nullable();
                } else {
                    $table->integer('level')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('designations') && Schema::hasColumn('designations', 'level')) {
            Schema::table('designations', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }
    }
};
