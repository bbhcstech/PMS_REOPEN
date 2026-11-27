<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('central_employee_invitation_links')) {
            Schema::create('central_employee_invitation_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('token_hash', 64)->unique();
                $table->string('token_type', 24)->default('invite'); // invite | correction
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'token_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('central_employee_invitation_links');
    }
};
