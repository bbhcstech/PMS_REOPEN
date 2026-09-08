<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lead_contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_contacts', 'lead_owner_designation')) {
                $table->string('lead_owner_designation')->nullable()->after('lead_owner_id');
            }
            if (!Schema::hasColumn('lead_contacts', 'added_by_designation')) {
                $table->string('added_by_designation')->nullable()->after('added_by');
            }
            if (!Schema::hasColumn('lead_contacts', 'converted_at')) {
                $table->timestamp('converted_at')->nullable();
            }
            if (!Schema::hasColumn('lead_contacts', 'converted_by')) {
                $table->unsignedBigInteger('converted_by')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_contacts', function (Blueprint $table) {
            $columns = ['lead_owner_designation', 'added_by_designation', 'converted_at', 'converted_by'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('lead_contacts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
