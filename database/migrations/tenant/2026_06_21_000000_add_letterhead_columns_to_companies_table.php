<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'letterhead_file')) {
                $table->string('letterhead_file')->nullable();
            }
            if (! Schema::hasColumn('companies', 'letterhead_original_name')) {
                $table->string('letterhead_original_name')->nullable();
            }
            if (! Schema::hasColumn('companies', 'letterhead_file_type')) {
                $table->string('letterhead_file_type')->nullable(); // 'pdf' or 'docs'
            }
            if (! Schema::hasColumn('companies', 'letterhead_uploaded_at')) {
                $table->timestamp('letterhead_uploaded_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            foreach ([
                'letterhead_file',
                'letterhead_original_name',
                'letterhead_file_type',
                'letterhead_uploaded_at',
            ] as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
