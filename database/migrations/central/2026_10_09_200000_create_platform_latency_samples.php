<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::connection('central')->hasTable('platform_latency_samples')) {
            Schema::connection('central')->create('platform_latency_samples', function (Blueprint $table) {
                $table->id();
                $table->double('api_ms');
                $table->double('db_ms');
                $table->timestamp('sampled_at')->index();
            });
        }
    }
    public function down(): void {
        Schema::connection('central')->dropIfExists('platform_latency_samples');
    }
};
