<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        $schema = Schema::connection('central');
        foreach (['requested_plan_id', 'requested_from_plan_id', 'plan_request_status', 'plan_reviewed_by', 'plan_reviewed_at'] as $column) {
            if (!$schema->hasColumn('company_complaints', $column)) $schema->table('company_complaints', function (Blueprint $t) use ($column) {
                if ($column === 'plan_reviewed_at') $t->timestamp($column)->nullable();
                elseif ($column === 'plan_request_status') $t->string($column, 20)->nullable()->index();
                else $t->unsignedBigInteger($column)->nullable();
            });
        }
        if (!$schema->hasColumn('companies', 'approved_plan_floor')) $schema->table('companies', fn (Blueprint $t) => $t->unsignedTinyInteger('approved_plan_floor')->nullable());
    }
    public function down(): void {
        Schema::connection('central')->table('company_complaints', fn (Blueprint $t) => $t->dropColumn(['requested_plan_id','requested_from_plan_id','plan_request_status','plan_reviewed_by','plan_reviewed_at']));
        Schema::connection('central')->table('companies', fn (Blueprint $t) => $t->dropColumn('approved_plan_floor'));
    }
};
