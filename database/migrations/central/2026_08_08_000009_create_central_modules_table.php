<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CENTRAL migration — runs against pms_central only.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('heroicon-o-cube');
            $table->text('description')->nullable();
            $table->string('route_prefix')->nullable();
            $table->string('route_name')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->boolean('is_core')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('modules');
    }
};
