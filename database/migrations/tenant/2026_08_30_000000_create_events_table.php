<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;
use App\Models\RolePermission;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->string('title');
                $table->string('slug')->nullable();
                $table->string('event_type')->default('Other');
                $table->text('description')->nullable();
                $table->string('banner')->nullable();
                $table->date('start_date')->index();
                $table->time('start_time')->nullable();
                $table->date('end_date')->nullable()->index();
                $table->time('end_time')->nullable();
                $table->string('location_type')->default('physical'); // physical, online, hybrid
                $table->string('location')->nullable();
                $table->string('meeting_url')->nullable();
                $table->unsignedBigInteger('organizer_id')->nullable()->index();
                $table->integer('max_participants')->nullable();
                $table->boolean('rsvp_required')->default(false);
                $table->string('reminder')->nullable();
                $table->string('status')->default('published')->index(); // draft, published, cancelled, completed
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('event_rsvps')) {
            Schema::create('event_rsvps', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('event_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->string('response'); // going, maybe, not_going
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();

                $table->unique(['event_id', 'user_id']);
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Register module in Module & RolePermission tables
        try {
            $module = Module::updateOrCreate(
                ['slug' => 'events'],
                [
                    'name' => 'Events',
                    'slug' => 'events',
                    'icon' => 'bx bx-calendar-event',
                    'route_name' => 'events.index',
                    'route_prefix' => 'events',
                    'sort_order' => 45,
                    'is_core' => true,
                    'is_active' => true,
                    'description' => 'Company events and important activities'
                ]
            );

            $roles = ['admin', 'manager', 'hr', 'employee'];
            foreach ($roles as $role) {
                $isCreatorRole = in_array($role, ['admin', 'manager', 'hr'], true);
                RolePermission::updateOrCreate(
                    ['role' => $role, 'module_id' => $module->id],
                    [
                        'can_view' => true,
                        'can_create' => $isCreatorRole,
                        'can_edit' => $isCreatorRole,
                        'can_delete' => $isCreatorRole,
                        'can_approve' => $isCreatorRole,
                        'can_export' => true,
                        'can_assign' => false,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Ignore if modules table is not present in central DB during tenant migrations
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_rsvps');
        Schema::dropIfExists('events');
    }
};
