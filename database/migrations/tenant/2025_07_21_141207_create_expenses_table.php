<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (! Schema::hasTable('expenses_category')) {
            Schema::create('expenses_category', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('category_name');
                $table->unsignedBigInteger('added_by')->nullable();
                $table->unsignedBigInteger('last_updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->string('item_name');
                $table->string('currency')->default('INR');
                $table->decimal('exchange_rate', 10, 2)->default(1);
                $table->decimal('price', 15, 2);
                $table->date('purchase_date');
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->unsignedBigInteger('project_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('purchased_from')->nullable();
                $table->unsignedBigInteger('bank_account_id')->nullable();
                $table->text('description')->nullable();
                $table->string('bill')->nullable();
                $table->timestamps();

                if (Schema::hasTable('users')) {
                    $table->foreign('employee_id')->references('id')->on('users')->nullOnDelete();
                }
                if (Schema::hasTable('projects')) {
                    $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
                }
                if (Schema::hasTable('expenses_category')) {
                    $table->foreign('category_id')->references('id')->on('expenses_category')->nullOnDelete();
                }
                if (Schema::hasTable('bank_accounts')) {
                    $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expenses_category');
    }
};
