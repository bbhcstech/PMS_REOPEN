<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('joining_date')->nullable();
            $table->integer('annual_leave_balance')->default(0);
            $table->integer('leaves_taken_this_year')->default(0);
            $table->integer('remaining_leaves')->default(0);
            $table->decimal('leave_amount', 10, 2)->nullable();
            $table->date('last_leave_reset')->nullable();
            $table->integer('carry_forward_leaves')->default(0);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'joining_date',
                'annual_leave_balance',
                'leaves_taken_this_year',
                'remaining_leaves',
                'leave_amount',
                'last_leave_reset',
                'carry_forward_leaves'
            ]);
        });
    }
};
