<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        // Legacy subscription enums do not include suspended. Keep lifecycle states
        // compatible with both MySQL and SQLite instead of swallowing failed writes.
        Schema::connection('central')->table('company_subscriptions', fn (Blueprint $t) => $t->string('status', 30)->default('trial')->change());
        Schema::connection('central')->table('companies', fn (Blueprint $t) => $t->string('status', 30)->default('trial')->change());
        foreach (['suspension_category' => 'string', 'suspension_reason' => 'text', 'suspended_by' => 'unsignedBigInteger'] as $column => $type) {
            if (!Schema::connection('central')->hasColumn('companies', $column)) {
                Schema::connection('central')->table('companies', fn (Blueprint $t) => $t->$type($column)->nullable());
            }
        }
    }
    public function down(): void {
        Schema::connection('central')->table('companies', fn (Blueprint $t) => $t->dropColumn(['suspension_category', 'suspension_reason', 'suspended_by']));
    }
};
