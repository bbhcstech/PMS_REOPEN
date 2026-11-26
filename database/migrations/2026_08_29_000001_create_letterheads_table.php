<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('letterheads')) {
            Schema::create('letterheads', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 100)->nullable();
                $table->string('type', 50)->default('company'); // company, branch, department, project, custom
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->unsignedBigInteger('project_id')->nullable();

                // Organization & contact info
                $table->string('company_name')->nullable();
                $table->string('tagline')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('department_name')->nullable();
                $table->string('project_name')->nullable();
                $table->string('address_line_1')->nullable();
                $table->string('address_line_2')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('state', 100)->nullable();
                $table->string('country', 100)->nullable();
                $table->string('postal_code', 50)->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('alternate_phone', 50)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('website', 200)->nullable();

                // Legal and corporate identification
                $table->string('registration_number', 100)->nullable();
                $table->string('tax_number', 100)->nullable();
                $table->string('gst_number', 100)->nullable();
                $table->string('cin_number', 100)->nullable();
                $table->text('other_info')->nullable();

                // Header styling & configuration
                $table->string('logo')->nullable();
                $table->string('logo_position', 20)->default('left'); // left, center, right
                $table->integer('logo_height')->default(52);
                $table->text('header_content')->nullable();
                $table->string('header_font', 100)->default('Plus Jakarta Sans');
                $table->integer('header_font_size')->default(14);
                $table->string('header_alignment', 20)->default('left');
                $table->string('header_border_style', 20)->default('solid'); // none, solid, double, dashed
                $table->integer('header_border_thickness')->default(2);
                $table->string('header_border_color', 50)->default('#0f744c');
                $table->integer('header_spacing')->default(20);
                $table->integer('header_height')->default(80);

                // Footer styling & configuration
                $table->text('footer_content')->nullable();
                $table->text('footer_text')->nullable();
                $table->integer('footer_font_size')->default(10);
                $table->string('footer_alignment', 20)->default('center');
                $table->string('footer_border_style', 20)->default('solid');
                $table->integer('footer_border_thickness')->default(1);
                $table->string('footer_border_color', 50)->default('#e2e8f0');
                $table->integer('footer_spacing')->default(15);
                $table->integer('footer_height')->default(50);

                // Page setup & margins
                $table->string('paper_size', 20)->default('a4'); // a4, letter, legal
                $table->string('orientation', 20)->default('portrait'); // portrait, landscape
                $table->integer('margin_top')->default(20);
                $table->integer('margin_bottom')->default(20);
                $table->integer('margin_left')->default(20);
                $table->integer('margin_right')->default(20);

                // Watermark configuration
                $table->boolean('watermark_enabled')->default(false);
                $table->string('watermark_type', 20)->default('text'); // text, image
                $table->string('watermark_text', 100)->nullable();
                $table->string('watermark_image')->nullable();
                $table->decimal('watermark_opacity', 3, 2)->default(0.10);
                $table->integer('watermark_rotation')->default(-45);
                $table->integer('watermark_size')->default(48);

                // Branding colors
                $table->string('primary_color', 50)->default('#0f744c');
                $table->string('secondary_color', 50)->default('#10b981');
                $table->string('header_line_color', 50)->default('#0f744c');
                $table->string('footer_line_color', 50)->default('#e2e8f0');

                // Status, versioning & metadata
                $table->string('status', 30)->default('active'); // active, draft, inactive, archived
                $table->boolean('is_default')->default(false);
                $table->integer('version')->default(1);
                $table->string('change_summary')->nullable();

                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('letterheads');
    }
};
