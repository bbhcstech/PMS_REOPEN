<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        $schema = Schema::connection($this->getConnection());

        if (! $schema->hasTable('recruitment_branding_assets')) {
            $schema->create('recruitment_branding_assets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('kind', 6);
                $table->string('name', 100);
                $table->string('mime_type', 20);
                $table->char('sha256', 64);
                $table->mediumText('image_data');
                $table->timestamps();
                $table->unique(['company_id', 'kind', 'name', 'sha256'], 'recruitment_branding_asset_unique');
            });
        }

        foreach (['pdf_header_asset_id', 'pdf_footer_asset_id'] as $column) {
            if (! $schema->hasColumn('recruitment_requirements', $column)) {
                $schema->table('recruitment_requirements', function (Blueprint $table) use ($column) {
                    $table->foreignId($column)->nullable()->constrained('recruitment_branding_assets')->restrictOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->getConnection());
        $schema->table('recruitment_requirements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pdf_header_asset_id');
            $table->dropConstrainedForeignId('pdf_footer_asset_id');
        });
        $schema->dropIfExists('recruitment_branding_assets');
    }
};
