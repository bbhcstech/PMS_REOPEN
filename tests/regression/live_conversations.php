<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Central\{Company, CompanyComplaint, ComplaintConversation, ComplaintAttachment};
use App\Models\User;
use App\Services\{ComplaintService, CompanyContext};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Validation\ValidationException;

foreach (['central', 'tenant'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Schema::connection('central')->create('companies', function ($t) { $t->id(); $t->string('name'); $t->softDeletes(); });
Schema::connection('central')->create('company_complaints', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id'); $t->string('ticket_id'); $t->string('status')->default('OPEN'); $t->softDeletes(); $t->timestamps();
});
Schema::connection('central')->create('complaint_conversations', function ($t) {
    $t->id(); $t->unsignedBigInteger('complaint_id'); $t->string('sender_type'); $t->unsignedBigInteger('sender_id');
    $t->string('sender_name'); $t->string('sender_email')->nullable(); $t->text('message'); $t->timestamps();
});
Schema::connection('central')->create('complaint_attachments', function ($t) {
    $t->id(); $t->unsignedBigInteger('complaint_id'); $t->unsignedBigInteger('conversation_id');
    $t->string('original_name'); $t->string('file_path'); $t->integer('file_size'); $t->timestamps();
});
Schema::connection('tenant')->create('users', function ($t) { $t->id(); $t->string('name'); $t->string('email'); $t->integer('company_id'); $t->string('role'); $t->timestamps(); });
DB::connection('central')->table('companies')->insert([['id' => 1, 'name' => 'Alpha'], ['id' => 2, 'name' => 'Beta']]);
$a = CompanyComplaint::create(['company_id' => 1, 'ticket_id' => 'ALPHA-1']);
$b = CompanyComplaint::create(['company_id' => 2, 'ticket_id' => 'BETA-1']);
$actor = new User(['name' => 'Alpha Admin', 'email' => 'admin@alpha.test', 'company_id' => 1, 'role' => 'admin']); $actor->id = 7;
Auth::guard('web')->setUser($actor); app(CompanyContext::class)->reset(Company::find(1));
// Prevent email/notification side effects while exercising the real response
// controllers and real central conversation writes.
$service = new class extends ComplaintService {
    public function addResponse(CompanyComplaint $complaint, string $message, $sender, string $senderType = 'super_admin', array $files = []): ComplaintConversation {
        return ComplaintConversation::create(['complaint_id' => $complaint->id, 'message' => $message,
            'sender_type' => $senderType, 'sender_id' => $sender->id, 'sender_name' => $sender->name]);
    }
};
$admin = new App\Http\Controllers\Admin\CompanyComplaintController($service);
$platform = new App\Http\Controllers\SuperAdmin\ComplaintController($service);
function liveCheck($ok, $message) { if (! $ok) throw new RuntimeException($message); }
function liveRequest($data = []) { $r = Request::create('/conversation', 'GET', $data, [], [], ['HTTP_ACCEPT' => 'application/json']); $r->setLaravelSession(app('session')->driver()); return $r; }
liveCheck($admin->messages(liveRequest(['after_id' => 0]), $a->id)->getData(true)['html'] === '', 'Empty chat failed.');
$response = $admin->reply(liveRequest(['message' => 'Hello platform']), $a->id);
liveCheck($response->getStatusCode() === 200 && $response->getData(true)['success'], 'Admin reply required navigation.');
$first = ComplaintConversation::firstOrFail();
ComplaintAttachment::create(['complaint_id' => $a->id, 'conversation_id' => $first->id, 'original_name' => '<script>alert(1)</script>.txt', 'file_path' => 'private/file.txt', 'file_size' => 12]);
$data = $admin->messages(liveRequest(['after_id' => 0]), $a->id)->getData(true);
liveCheck($data['last_id'] === $first->id && str_contains($data['html'], 'data-message-id') && str_contains($data['html'], '&lt;script&gt;') && !str_contains($data['html'], '<script>alert'), 'Message/attachment rendering was missing or unsafe.');
liveCheck($admin->messages(liveRequest(['after_id' => $first->id]), $a->id)->getData(true)['html'] === '', 'Cursor resent old messages.');
try { $admin->messages(liveRequest(), $b->id); throw new RuntimeException('Foreign company chat leaked.'); }
catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {}
try { $platform->messages(liveRequest(), $a->id); throw new RuntimeException('Tenant admin accessed platform feed.'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { liveCheck($e->getStatusCode() === 403, 'Wrong access failure.'); }
$platformActor = new User(['name' => 'Platform', 'role' => 'superadmin']); $platformActor->id = 1;
Auth::guard('web')->setUser($platformActor);
$response = $platform->respond(liveRequest(['message' => 'Platform answer']), $a->id);
liveCheck($response->getStatusCode() === 200, 'Platform reply failed.');
$data = $platform->messages(liveRequest(['after_id' => $first->id]), $a->id)->getData(true);
liveCheck(str_contains($data['html'], 'Platform answer') && !str_contains($data['html'], 'Hello platform'), 'Platform delta incorrect.');
try { $platform->messages(liveRequest(['after_id' => -1]), $a->id); throw new RuntimeException('Negative cursor accepted.'); }
catch (ValidationException $e) {}
for ($i = 0; $i < 105; $i++) $service->addResponse($a, 'Backlog ' . $i, $actor, 'company_admin');
$data = $platform->messages(liveRequest(['after_id' => 2]), $a->id)->getData(true);
liveCheck($data['has_more'] && substr_count($data['html'], 'data-message-id=') === 100, 'Feed did not bound backlog size.');
liveCheck(!Schema::connection('tenant')->hasTable('complaint_conversations'), 'Conversation data entered tenant database.');
echo "PASS: support replies without navigation, empty/delta/backlog feeds, safe attachments and company/platform authorization.\n";

// Exercise the empty-room cursor for every company role against real tenant tables.
(require database_path('migrations/tenant/2026_08_30_000002_create_community_messages_tables.php'))->up();
DB::connection('tenant')->table('users')->insert(['id' => 7, 'name' => 'Sender', 'email' => 'sender@test.local', 'company_id' => 1, 'role' => 'admin']);
Auth::guard('web')->setUser($actor);
$community = new App\Http\Controllers\CommunityMessageController();
liveCheck($community->fetchMessages(liveRequest(['after_id' => 0]))->getData(true)['messages'] === [], 'Empty community failed.');
$shared = App\Models\CommunityMessage::create(['company_id' => 1, 'user_id' => 7, 'message' => 'Shared with company roles']);
App\Models\CommunityMessage::create(['company_id' => 2, 'user_id' => 7, 'message' => 'Foreign company message']);
foreach (['admin', 'hr', 'manager', 'employee'] as $role) {
    $actor->role = $role;
    $data = $community->fetchMessages(liveRequest(['after_id' => 0]))->getData(true);
    liveCheck(count($data['messages']) === 1 && $data['messages'][0]['id'] === $shared->id, "$role did not receive an isolated company message.");
    liveCheck($community->fetchMessages(liveRequest(['after_id' => $shared->id]))->getData(true)['messages'] === [], 'Community cursor repeated a message.');
}
app(CompanyContext::class)->reset(Company::find(2));
try { $community->fetchMessages(liveRequest(['after_id' => 0])); throw new RuntimeException('Mismatched company context accepted.'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { liveCheck($e->getStatusCode() === 403, 'Wrong community access failure.'); }
echo "PASS: empty community, messages across company roles, cursor deduplication and tenant isolation.\n";
