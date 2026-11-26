<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recruitment_requirements')) {
            return;
        }
        Schema::table('recruitment_requirements', function (Blueprint $table) {
            if (! Schema::hasColumn('recruitment_requirements', 'pdf_header_image')) {
                $table->string('pdf_header_image')->nullable();
            }
            if (! Schema::hasColumn('recruitment_requirements', 'pdf_footer_image')) {
                $table->string('pdf_footer_image')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_requirements', function (Blueprint $table) {
            $table->dropColumn(['pdf_header_image', 'pdf_footer_image']);
        });
    }
};
