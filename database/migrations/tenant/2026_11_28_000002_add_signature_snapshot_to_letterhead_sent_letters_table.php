<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('letterhead_sent_letters')) {
            return;
        }

        Schema::table('letterhead_sent_letters', function (Blueprint $table) {
            if (! Schema::hasColumn('letterhead_sent_letters', 'signature_id')) {
                $table->unsignedBigInteger('signature_id')->nullable()->after('signatory_title')->index();
            }
            if (! Schema::hasColumn('letterhead_sent_letters', 'signature_snapshot_path')) {
                $table->string('signature_snapshot_path')->nullable()->after('signature_id');
            }
            if (! Schema::hasColumn('letterhead_sent_letters', 'signature_alignment')) {
                $table->string('signature_alignment', 16)->nullable()->after('signature_snapshot_path');
            }
            if (! Schema::hasColumn('letterhead_sent_letters', 'signature_width_mm')) {
                $table->unsignedSmallInteger('signature_width_mm')->nullable()->after('signature_alignment');
            }
            if (! Schema::hasColumn('letterhead_sent_letters', 'signature_applied_by')) {
                $table->unsignedBigInteger('signature_applied_by')->nullable()->after('signature_width_mm');
            }
            if (! Schema::hasColumn('letterhead_sent_letters', 'signature_applied_at')) {
                $table->timestamp('signature_applied_at')->nullable()->after('signature_applied_by');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('letterhead_sent_letters')) {
            return;
        }

        Schema::table('letterhead_sent_letters', function (Blueprint $table) {
            foreach ([
                'signature_id',
                'signature_snapshot_path',
                'signature_alignment',
                'signature_width_mm',
                'signature_applied_by',
                'signature_applied_at',
            ] as $col) {
                if (Schema::hasColumn('letterhead_sent_letters', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
