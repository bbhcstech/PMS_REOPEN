<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CENTRAL migration.
 * plans: subscription plan catalog shared across all companies.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->integer('max_users')->default(0)->comment('0 = unlimited');
            $table->integer('max_projects')->default(0)->comment('0 = unlimited');
            $table->integer('max_clients')->default(0)->comment('0 = unlimited');
            $table->bigInteger('max_storage_mb')->default(0)->comment('0 = unlimited');
            $table->json('features')->nullable()->comment('JSON array of feature slugs included');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('plans');
    }
};
