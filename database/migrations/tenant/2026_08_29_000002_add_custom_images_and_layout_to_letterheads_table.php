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
        if (Schema::hasTable('letterheads')) {
            Schema::table('letterheads', function (Blueprint $table) {
                if (! Schema::hasColumn('letterheads', 'header_image')) {
                    $table->string('header_image')->nullable()->after('logo');
                }
                if (! Schema::hasColumn('letterheads', 'footer_image')) {
                    $table->string('footer_image')->nullable()->after('header_image');
                }
                if (! Schema::hasColumn('letterheads', 'background_page_image')) {
                    $table->string('background_page_image')->nullable()->after('footer_image');
                }
                if (! Schema::hasColumn('letterheads', 'layout_mode')) {
                    $table->string('layout_mode', 50)->default('standard')->after('background_page_image'); // standard, custom_header_footer, full_a4_page
                }
                if (! Schema::hasColumn('letterheads', 'content_padding_top')) {
                    $table->integer('content_padding_top')->default(140)->after('layout_mode');
                }
                if (! Schema::hasColumn('letterheads', 'content_padding_bottom')) {
                    $table->integer('content_padding_bottom')->default(120)->after('content_padding_top');
                }
                if (! Schema::hasColumn('letterheads', 'content_padding_left')) {
                    $table->integer('content_padding_left')->default(65)->after('content_padding_bottom');
                }
                if (! Schema::hasColumn('letterheads', 'content_padding_right')) {
                    $table->integer('content_padding_right')->default(65)->after('content_padding_left');
                }
                if (! Schema::hasColumn('letterheads', 'preset_template')) {
                    $table->string('preset_template', 100)->nullable()->after('content_padding_right');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('letterheads')) {
            Schema::table('letterheads', function (Blueprint $table) {
                $columns = [
                    'header_image',
                    'footer_image',
                    'background_page_image',
                    'layout_mode',
                    'content_padding_top',
                    'content_padding_bottom',
                    'content_padding_left',
                    'content_padding_right',
                    'preset_template',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('letterheads', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
