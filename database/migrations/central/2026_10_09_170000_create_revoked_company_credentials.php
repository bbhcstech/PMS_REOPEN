<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::connection('central')->hasTable('revoked_company_credentials')) return;
        Schema::connection('central')->create('revoked_company_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->char('login_fingerprint', 64)->index();
            $table->string('password_hash');
            $table->timestamp('created_at');
        });
    }
    public function down(): void { Schema::connection('central')->dropIfExists('revoked_company_credentials'); }
};
