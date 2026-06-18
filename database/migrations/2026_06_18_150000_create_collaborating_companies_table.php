<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborating_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('industry')->nullable();
            $table->string('collaboration_type')->nullable();
            $table->text('description')->nullable();
            $table->text('services')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            $table->string('status')->default('active')->index();
            $table->date('started_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborating_companies');
    }
};
