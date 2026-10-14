<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('letterhead_sent_letters')) {
            return;
        }
        Schema::create('letterhead_sent_letters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('letterhead_id')->nullable()->index();
            $table->string('template_key', 100)->nullable();
            $table->string('ref_no', 120)->nullable();
            $table->string('letter_date', 60)->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('subject', 300);
            $table->longText('body');
            $table->string('signatory_name')->nullable();
            $table->string('signatory_title')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('delivery_status', 20)->default('saved');
            $table->text('delivery_error')->nullable();
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letterhead_sent_letters');
    }
};
