<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::connection('central')->hasTable('platform_alert_reads')) {
            Schema::connection('central')->create('platform_alert_reads', function (Blueprint $table) {
                $table->string('alert_key', 64)->primary();
                $table->timestamp('read_at');
            });
        }
    }
    public function down(): void { Schema::connection('central')->dropIfExists('platform_alert_reads'); }
};
