<?php

// Run with: php tests/regression/community_message_deletion.php (isolated SQLite database).
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\CommunityMessageController;
use App\Models\{CommunityMessage, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'session.driver' => 'array']);
DB::purge('tenant');
DB::connection('tenant')->statement('CREATE TABLE community_messages (id integer primary key, company_id integer, user_id integer, message text, is_pinned integer, deleted_by integer, deleted_at text, created_at text, updated_at text)');
$controller = new class extends CommunityMessageController {
    protected function getCompanyId(): int { return (int) Auth::user()->company_id; }
};
$formatter = new ReflectionMethod(CommunityMessageController::class, 'formatMessage');
function checkCommunity(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
function fixtureMessage(int $owner, int $company = 1): CommunityMessage {
    $message = CommunityMessage::create(['company_id' => $company, 'user_id' => $owner, 'message' => 'Test message', 'is_pinned' => true]);
    $message->setRelation('user', new User(['id' => $owner, 'name' => 'Sender', 'role' => 'hr']));
    $message->setRelation('parent', null);
    $message->setRelation('reactions', collect());
    return $message;
}
foreach (['manager', 'hr', 'employee', 'developer'] as $role) {
    $actor = new User(['name' => 'Actor', 'role' => $role, 'company_id' => 1]); $actor->id = 10;
    Auth::guard('web')->setUser($actor);
    $message = fixtureMessage(20);
    checkCommunity(!$formatter->invoke($controller, $message, $actor)['can_delete'], "{$role} sees delete for someone else's message.");
    checkCommunity($controller->destroy(Request::create('/community', 'DELETE'), $message->id)->getStatusCode() === 403, "{$role} deleted someone else's message.");
    checkCommunity(! $message->fresh()->trashed() && $message->fresh()->is_pinned, 'Denied deletion changed the message.');
    $own = fixtureMessage(10);
    checkCommunity($formatter->invoke($controller, $own, $actor)['can_delete'], 'Own-message delete control missing.');
    checkCommunity($controller->destroy(Request::create('/community', 'DELETE'), $own->id)->getStatusCode() === 200, 'Own-message deletion denied.');
    checkCommunity(CommunityMessage::withTrashed()->find($own->id)->trashed(), 'Own-message deletion did not persist.');
}
$admin = new User(['name' => 'Admin', 'role' => 'admin', 'company_id' => 1]); $admin->id = 1;
Auth::guard('web')->setUser($admin);
$message = fixtureMessage(20);
checkCommunity($formatter->invoke($controller, $message, $admin)['can_delete'], 'Admin moderation control missing.');
$response = $controller->destroy(Request::create('/community', 'DELETE'), $message->id);
checkCommunity($response->getData(true)['is_moderated'], 'Admin deletion was not marked as moderated.');
$deleted = CommunityMessage::withTrashed()->find($message->id);
checkCommunity($deleted->trashed() && !$deleted->is_pinned && (int) $deleted->deleted_by === 1, 'Admin deletion audit or pin state incorrect.');
$foreign = fixtureMessage(20, 2);
checkCommunity(!$formatter->invoke($controller, $foreign, $admin)['can_delete'], 'Admin can delete another company message.');
try {
    $controller->destroy(Request::create('/community', 'DELETE'), $foreign->id);
    throw new RuntimeException('Cross-company delete was accepted.');
} catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {}
echo "Community deletion checks passed: own messages, admin-only moderation, UI permissions, denied writes and company isolation.\n";
