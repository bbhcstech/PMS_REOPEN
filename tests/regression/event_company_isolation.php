<?php

// Run with: php tests/regression/event_company_isolation.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\EventController;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

foreach (['tenant', 'mysql', 'central', 'session_db'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array', 'cache.default' => 'array']);
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->string('name');
    $table->string('role');
    $table->boolean('is_active')->nullable();
});
DB::table('users')->insert([
    ['id' => 1, 'company_id' => 1, 'name' => 'Current admin', 'role' => 'admin'],
    ['id' => 2, 'company_id' => 1, 'name' => 'Local HR', 'role' => 'hr'],
    ['id' => 3, 'company_id' => 2, 'name' => 'Foreign HR', 'role' => 'hr'],
    ['id' => 4, 'company_id' => 1, 'name' => 'Employee', 'role' => 'employee'],
    ['id' => 5, 'company_id' => 1, 'name' => 'Duplicate admin', 'role' => 'admin'],
    ['id' => 6, 'company_id' => 1, 'name' => 'Manager', 'role' => 'manager'],
    ['id' => 7, 'company_id' => null, 'name' => 'Unassigned HR', 'role' => 'hr'],
]);
$actor = User::findOrFail(1);
Auth::guard('web')->setUser($actor);
app(CompanyContext::class)->reset((object) ['id' => 1]);
(require database_path('migrations/tenant/2026_08_30_000000_create_events_table.php'))->up();
(require database_path('migrations/tenant/2026_08_30_000001_create_event_photos_table.php'))->up();

$controller = new EventController;
function checkEventIsolation(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}
$indexRequest = Request::create('/events', 'GET');
checkEventIsolation($controller->index($indexRequest)->getData()['users']->pluck('id')->all() === [2], 'Organizer dropdown leaked users or included admins/employees');
DB::table('users')->where('id', 2)->update(['role' => 'employee']);
checkEventIsolation($controller->index($indexRequest)->getData()['users']->isEmpty(), 'No-HR company received unrelated organizer choices');
DB::table('users')->where('id', 2)->update(['role' => 'hr']);

$payload = ['title' => 'Annual day', 'event_type' => 'Meeting', 'description' => 'Test event',
    'start_date' => '2026-10-08', 'end_date' => '2026-10-10', 'start_time' => '09:00',
    'end_time' => '10:00', 'location_type' => 'physical', 'status' => 'draft'];
foreach (['start_date' => ['invalid-date'], 'start_time' => '25:99', 'end_time' => ['invalid-time']] as $field => $value) {
    $rejected = false;
    try {
        $controller->store(Request::create('/events', 'POST', array_replace($payload, [$field => $value])));
    } catch (ValidationException $exception) {
        $rejected = isset($exception->errors()[$field]);
    }
    checkEventIsolation($rejected, "Invalid {$field} caused a server error or was accepted");
}
foreach ([3, 4, 5, 6, 7, 999] as $organizerId) {
    $rejected = false;
    try {
        $controller->store(Request::create('/events', 'POST', $payload + ['organizer_id' => $organizerId]));
    } catch (ValidationException $exception) {
        $rejected = isset($exception->errors()['organizer_id']);
    }
    checkEventIsolation($rejected && DB::table('events')->count() === 0, "Invalid organizer {$organizerId} was accepted");
}

$create = Request::create('/events', 'POST', $payload + ['organizer_id' => 2]);
$create->headers->set('Accept', 'application/json');
$created = $controller->store($create)->getData(true)['event'];
$eventId = $created['id'];
checkEventIsolation($created['organizer_id'] == 2 && $created['company_id'] == 1, 'Local HR organizer was not accepted');

$defaultCreate = Request::create('/events', 'POST', $payload);
$defaultCreate->headers->set('Accept', 'application/json');
checkEventIsolation($controller->store($defaultCreate)->getData(true)['event']['organizer_id'] == 1, 'Implicit current admin organizer failed');

DB::table('events')->where('id', $eventId)->update(['organizer_id' => 3, 'created_by' => 3, 'banner' => 'https://example.com/banner.png']);
DB::table('event_photos')->insert([
    ['company_id' => 1, 'event_id' => $eventId, 'uploaded_by' => 3, 'image_path' => 'test/local.png'],
    ['company_id' => 2, 'event_id' => $eventId, 'uploaded_by' => 2, 'image_path' => 'test/foreign.png'],
]);
DB::table('event_rsvps')->insert([
    ['company_id' => 1, 'event_id' => $eventId, 'user_id' => 3, 'response' => 'going'],
    ['company_id' => 2, 'event_id' => $eventId, 'user_id' => 2, 'response' => 'going'],
]);
$details = $controller->show(Request::create('/events/' . $eventId), $eventId)->getData(true);
checkEventIsolation($details['event']['organizer'] === null && $details['event']['creator'] === null, 'Foreign organizer or creator details leaked');
checkEventIsolation(count($details['photos']) === 1 && $details['photos_count'] === 1, 'Foreign event photo leaked');
checkEventIsolation($details['photos'][0]['uploader'] === null, 'Foreign photo uploader details leaked');
checkEventIsolation(count($details['event']['rsvps']) === 1 && $details['event']['rsvps'][0]['user'] === null, 'Foreign RSVP user details leaked');
checkEventIsolation($details['rsvp_counts']['total'] === 1, 'Foreign RSVP count leaked');
checkEventIsolation($controller->getPhotos($eventId)->getData(true)['photos'][0]['uploader'] === null, 'Photo endpoint leaked foreign user');

$monthRequest = Request::create('/events/calendar-data', 'GET', ['start' => '2026-10-01', 'end' => '2026-11-01', 'view' => 'dayGridMonth']);
$feed = collect($controller->calendarData($monthRequest)->getData(true))->keyBy('id');
checkEventIsolation($feed[$eventId]['start'] === '2026-10-08' && $feed[$eventId]['end'] === '2026-10-09' && $feed[$eventId]['allDay'], 'Banner was not confined to its single date');
checkEventIsolation($feed[$eventId]['extendedProps']['display_time'] === '09:00 AM', 'Banner lost event time');
$weekRequest = Request::create('/events/calendar-data', 'GET', ['view' => 'timeGridWeek']);
$feed = collect($controller->calendarData($weekRequest)->getData(true))->keyBy('id');
checkEventIsolation($feed[$eventId]['start'] === '2026-10-08T09:00:00' && $feed[$eventId]['end'] === '2026-10-10T10:00:00', 'Actual event duration changed');

$update = Request::create('/events/' . $eventId, 'POST', array_replace($payload, ['start_date' => '2026-10-12', 'end_date' => '2026-10-15', 'organizer_id' => 2]));
$update->headers->set('Accept', 'application/json');
checkEventIsolation($controller->update($update, $eventId)->getData(true)['success'], 'Date update failed');
$feed = collect($controller->calendarData($monthRequest)->getData(true))->keyBy('id');
checkEventIsolation($feed[$eventId]['start'] === '2026-10-12' && $feed[$eventId]['end'] === '2026-10-13', 'Updated banner remained on the old date or spanned dates');

$update->merge(['start_date' => '2026-11-02', 'end_date' => '2026-11-05']);
$controller->update($update, $eventId);
$feed = collect($controller->calendarData($monthRequest)->getData(true))->keyBy('id');
checkEventIsolation(!$feed->has($eventId), 'Moving event to a new month left the old banner');
$monthRequest = Request::create('/events/calendar-data', 'GET', ['start' => '2026-11-01', 'end' => '2026-12-01', 'view' => 'dayGridMonth']);
$feed = collect($controller->calendarData($monthRequest)->getData(true))->keyBy('id');
checkEventIsolation($feed[$eventId]['start'] === '2026-11-02' && $feed[$eventId]['end'] === '2026-11-03', 'Banner did not move to a single date in the new month');

DB::table('events')->insert(['id' => 100, 'company_id' => 2, 'title' => 'Foreign event', 'start_date' => '2026-10-08']);
foreach (['show', 'update', 'destroy'] as $action) {
    $rejected = false;
    try {
        $controller->$action($update, 100);
    } catch (Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
        $rejected = true;
    }
    checkEventIsolation($rejected, "Foreign event {$action} was allowed");
}

$bannerPath = 'uploads/events/banners/event-regression-' . bin2hex(random_bytes(6)) . '.png';
$bannerFile = public_path($bannerPath);
if (!is_dir(dirname($bannerFile))) mkdir(dirname($bannerFile), 0755, true);
file_put_contents($bannerFile, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aB3sAAAAASUVORK5CYII='));
try {
    DB::table('events')->where('id', $eventId)->update(['banner' => $bannerPath]);
    $feed = collect($controller->calendarData($monthRequest)->getData(true))->keyBy('id');
    checkEventIsolation($feed[$eventId]['extendedProps']['banner_url'] === asset($bannerPath), 'Local banner URL did not resolve');
    $delete = Request::create('/events/' . $eventId, 'DELETE');
    $delete->headers->set('Accept', 'application/json');
    checkEventIsolation($controller->destroy($delete, $eventId)->getData(true)['success'], 'Event deletion failed');
    checkEventIsolation(!file_exists($bannerFile), 'Deleted event left its banner file behind');
} finally {
    if (file_exists($bannerFile)) unlink($bannerFile);
}
$feed = collect($controller->calendarData($monthRequest)->getData(true))->keyBy('id');
checkEventIsolation(!$feed->has($eventId) && !$feed->has(100), 'Deleted or foreign banner remained in calendar');

Schema::create('notifications', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('type');
    $table->morphs('notifiable');
    $table->text('data');
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
});
$notificationController = new class extends EventController {
    public function notifyFixture(): void { $this->notifyCompanyUsers('Event test', 'Event test'); }
};
$notificationController->notifyFixture();
$recipients = DB::table('notifications')->orderBy('notifiable_id')->pluck('notifiable_id')->all();
checkEventIsolation($recipients === [1, 2, 4, 5, 6], 'Event notifications leaked to another company or unassigned users');

app(CompanyContext::class)->reset((object) ['id' => 2]);
$rejected = false;
try {
    $controller->calendarData($monthRequest);
} catch (Symfony\Component\HttpKernel\Exception\HttpException $exception) {
    $rejected = $exception->getStatusCode() === 403;
}
checkEventIsolation($rejected, 'Mismatched company context exposed another company');

$unknownActor = clone $actor;
$unknownActor->company_id = null;
Auth::guard('web')->setUser($unknownActor);
app(CompanyContext::class)->reset();
$rejected = false;
try {
    $controller->index($indexRequest);
} catch (Symfony\Component\HttpKernel\Exception\HttpException $exception) {
    $rejected = $exception->getStatusCode() === 403;
}
checkEventIsolation($rejected, 'Missing company context exposed unfiltered data');
echo "PASS: Organizer choices, forged organizer rejection, related-user isolation, single-date banners, date changes, deletion, foreign-event rejection, and missing-company rejection.\n";
