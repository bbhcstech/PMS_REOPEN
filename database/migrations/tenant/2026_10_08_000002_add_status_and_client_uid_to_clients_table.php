<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (! Schema::hasColumn('clients', 'status')) {
                    $table->string('status', 50)->default('active')->after('note');
                }
                if (! Schema::hasColumn('clients', 'client_uid')) {
                    $table->string('client_uid', 100)->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('clients', 'status')) {
                    $columns[] = 'status';
                }
                if (Schema::hasColumn('clients', 'client_uid')) {
                    $columns[] = 'client_uid';
                }
                if (! empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
