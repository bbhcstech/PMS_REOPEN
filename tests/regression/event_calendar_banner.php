<?php

// Run with: php tests/regression/event_calendar_banner.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\EventController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config([
    'database.default' => 'tenant',
    'database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
    'session.driver' => 'array',
]);
DB::purge('tenant');
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id');
    $table->string('title');
    $table->string('banner')->nullable();
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->time('start_time')->nullable();
    $table->time('end_time')->nullable();
    $table->string('status');
    $table->string('event_type')->default('Meeting');
    $table->string('location_type')->default('physical');
    $table->string('location')->nullable();
    $table->string('meeting_url')->nullable();
    $table->unsignedBigInteger('organizer_id')->nullable();
    $table->boolean('rsvp_required')->default(false);
    $table->softDeletes();
});

$base = ['company_id' => 1, 'title' => 'Banner event', 'start_date' => '2026-10-08',
    'start_time' => '09:00:00', 'end_date' => '2026-10-08', 'end_time' => '10:00:00', 'status' => 'published'];
DB::table('events')->insert($base + ['id' => 1, 'banner' => 'https://example.com/event-banner.png']);
DB::table('events')->insert(array_replace($base, ['id' => 2, 'title' => 'No banner', 'banner' => null]));
DB::table('events')->insert(array_replace($base, ['id' => 3, 'company_id' => 2, 'banner' => 'https://example.com/other-tenant.png']));
DB::table('events')->insert(array_replace($base, ['id' => 4, 'status' => 'draft']));
DB::table('events')->insert(array_replace($base, ['id' => 5, 'banner' => 'uploads/events/banners/missing-regression-banner.png']));

$controller = new class extends EventController {
    public bool $manage = true;
    protected function getCompanyId(): ?int { return 1; }
    protected function canManageEvents(?App\Models\User $user = null): bool { return $this->manage; }
};
$request = Request::create('/events/calendar-data', 'GET', ['start' => '2026-10-01', 'end' => '2026-11-01']);
$events = collect($controller->calendarData($request)->getData(true))->keyBy('id');
function checkEventBanner(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}
checkEventBanner($events->count() === 4 && !$events->has(3), 'Tenant isolation changed');
checkEventBanner($events[1]['extendedProps']['banner_url'] === 'https://example.com/event-banner.png', 'Banner URL missing from calendar feed');
checkEventBanner($events[1]['start'] === '2026-10-08T09:00:00', 'Event date or time changed');
checkEventBanner($events[1]['end'] === '2026-10-08T10:00:00', 'Event end changed');
checkEventBanner($events[2]['extendedProps']['banner_url'] === null, 'Missing banner fallback failed');
checkEventBanner($events[5]['extendedProps']['banner_url'] === null, 'Missing local file fallback failed');
$controller->manage = false;
$events = collect($controller->calendarData($request)->getData(true))->keyBy('id');
checkEventBanner(!$events->has(4), 'Draft visibility changed');
echo "PASS: Calendar banner feed, event dates, missing banners, tenant isolation, and draft visibility.\n";
