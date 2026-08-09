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
        if (! Schema::hasTable('task_labels')) {
            Schema::create('task_labels', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('label_id');
                $table->unsignedBigInteger('task_id');
                $table->timestamps();

                if (Schema::hasTable('task_label_list')) {
                    $table->foreign('label_id')->references('id')->on('task_label_list')->onDelete('cascade');
                }
                if (Schema::hasTable('tasks')) {
                    $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
                }

                $table->unique(['label_id', 'task_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_labels');
    }
};
