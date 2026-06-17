<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sticky_notes')) {
            Schema::create('sticky_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('note_text');
                $table->string('colour', 30)->default('yellow');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('sticky_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('sticky_notes', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->index()->after('id');
            }

            if (! Schema::hasColumn('sticky_notes', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('colour');
            }

            if (! Schema::hasColumn('sticky_notes', 'colour')) {
                $table->string('colour', 30)->default('yellow')->after('note_text');
            }

            if (! Schema::hasColumn('sticky_notes', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sticky_notes', function (Blueprint $table) {
            if (Schema::hasColumn('sticky_notes', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};
