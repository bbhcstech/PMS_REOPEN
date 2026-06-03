<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->string('domain')->unique()->nullable();
            $table->string('subdomain')->unique()->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'suspended', 'trial', 'inactive'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->integer('max_users')->default(10);
            $table->integer('max_projects')->default(5);
            $table->integer('max_clients')->default(50);
            $table->bigInteger('max_storage_mb')->default(1024);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('companies'); }
};
