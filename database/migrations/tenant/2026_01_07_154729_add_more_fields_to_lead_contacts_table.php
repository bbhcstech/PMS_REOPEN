<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lead_contacts', function (Blueprint $table) {
            // Add new columns for contact information
            // $table->string('salutation')->nullable();
            // $table->string('mobile')->nullable();

            // Add new columns for company information
            // $table->string('website')->nullable();
            // $table->string('phone')->nullable();
            // $table->text('address')->nullable();
            // $table->string('city')->nullable();
            // $table->string('state')->nullable();
            // $table->string('country')->nullable();
            // $table->string('postal_code')->nullable();
            // $table->string('industry')->nullable();

            // Add new columns for lead source & status
            // $table->string('lead_source')->nullable();
            // $table->string('status')->default('new');
            // $table->integer('lead_score')->default(0);
            // $table->text('tags')->nullable();

            // Add new columns for deal information
            // $table->boolean('create_deal')->default(false);
            // $table->string('deal_name')->nullable();
            // $table->decimal('deal_value', 15, 2)->nullable();
            // $table->string('deal_currency')->default('INR');
            // $table->unsignedBigInteger('deal_agent_id')->nullable();
            // $table->string('pipeline')->nullable();
            // $table->string('deal_stage')->nullable();
            // $table->string('deal_category')->nullable();
            // $table->date('close_date')->nullable();
            // $table->json('products')->nullable();

            // // Add new column for description
            // $table->text('description')->nullable();

            // // Add foreign key for deal_agent_id
            // $table->foreign('deal_agent_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_contacts', function (Blueprint $table) {
            // Drop the columns in reverse order
            $table->dropForeign(['deal_agent_id']);
            $table->dropColumn([
                'salutation',
                'mobile',
                'website',
                'phone',
                'address',
                'city',
                'state',
                'country',
                'postal_code',
                'industry',
                'lead_source',
                'status',
                'lead_score',
                'tags',
                'create_deal',
                'deal_name',
                'deal_value',
                'deal_currency',
                'deal_agent_id',
                'pipeline',
                'deal_stage',
                'deal_category',
                'close_date',
                'products',
                'description'
            ]);
        });
    }
};
