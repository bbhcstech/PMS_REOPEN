<?php

require __DIR__.'/authority_admin_delivery.php';

use App\Http\Controllers\TicketController;
use App\Http\Middleware\EnsureModuleAccess;
use App\Models\{Ticket, TicketActivity, User};
use App\Support\TicketAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Notification, Schema};
use Illuminate\Database\Schema\Blueprint;

DB::statement('ALTER TABLE users ADD COLUMN archived_at text');
DB::statement('ALTER TABLE users ADD COLUMN email text');
Schema::create('tickets', function (Blueprint $t) {
    $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->unsignedBigInteger('requester_id');
    $t->unsignedBigInteger('agent_id')->nullable(); $t->unsignedBigInteger('project_id')->nullable(); $t->unsignedBigInteger('group_id')->nullable();
    foreach (['requester_type','requester_name','affected_module','type_id','subject','description','attachment','priority','deadline','channel','tags','status'] as $column) $t->text($column)->nullable();
    $t->timestamps();
});
Schema::create('ticket_activities', function (Blueprint $t) {
    $t->id(); foreach (['ticket_id','project_id','user_id','assigned_to','channel_id','group_id','type_id'] as $column) $t->unsignedBigInteger($column)->nullable();
    foreach (['status','priority','type','content'] as $column) $t->text($column)->nullable();
    $t->timestamps();
});
Schema::create('projects', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('company_id'); $t->string('name'); });
Schema::create('ticket_groups', function (Blueprint $t) { $t->id(); $t->string('group_name'); $t->timestamps(); });
Schema::create('replies', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('ticket_id'); $t->unsignedBigInteger('user_id'); $t->text('message'); $t->string('attachment')->nullable(); $t->timestamps(); });
Schema::create('modules', function (Blueprint $t) { $t->id(); $t->string('slug'); $t->string('name'); $t->boolean('is_active')->default(true); $t->integer('sort_order')->default(0); $t->unsignedBigInteger('parent_id')->nullable(); });
DB::table('modules')->insert(['slug'=>'tickets','name'=>'Internal Tickets']);
Auth::guard('web')->setUser(User::findOrFail(1));
$controller = new TicketController;
Notification::fake();
$payload = ['requester_type'=>'employee', 'requester_name'=>1, 'agent_id'=>7, 'subject'=>'Internal assignment', 'description'=>'Complete internal work', 'priority'=>'high'];
$response = $controller->store(Request::create('/tickets', 'POST', $payload));
$assert($response->getStatusCode() === 302 && Ticket::count() === 1, 'Admin could not raise an internal ticket without a project.');
$ticket = Ticket::firstOrFail();
$assert($ticket->company_id === 1 && (int) $ticket->agent_id === 7 && $ticket->status === 'open', 'Ticket assignment or company was not saved.');
$assert(!TicketAccess::hiddenForCurrentUser(), 'Company admin ticket module is hidden.');
$assert(Notification::sent(User::findOrFail(7), App\Notifications\SystemNotification::class)->isNotEmpty(), 'Assigned developer not notified.');
$assert(Notification::sent(User::findOrFail(5), App\Notifications\SystemNotification::class)->isEmpty(), 'Unrelated HR received private ticket notification.');
Auth::guard('web')->setUser(User::findOrFail(7));
foreach (['open','in_progress','pending','resolved'] as $status) {
    $request=Request::create('/tickets/change-status','POST',['ticketId'=>$ticket->id,'status'=>$status]);
    $request->headers->set('Accept','application/json');
    $assert($controller->changeStatus($request)->getStatusCode() === 200 && $ticket->fresh()->status === $status, 'Assigned developer progress did not persist.');
}
$assert(TicketActivity::where('ticket_id',$ticket->id)->count() === 5, 'Progress history is incomplete.');
Notification::fake();
DB::connection('tenant')->beginTransaction();
$controller->changeStatus(Request::create('/status','POST',['ticketId'=>$ticket->id,'status'=>'in_progress']));
DB::connection('tenant')->rollBack();
$assert($ticket->fresh()->status === 'resolved' && TicketActivity::where('ticket_id',$ticket->id)->count() === 5, 'Rolled-back progress changed saved history.');
$assert(Notification::sentNotifications() === [], 'Rolled-back progress sent a notification.');
$controller->reply(Request::create('/tickets/'.$ticket->id.'/reply','POST',['message'=>'Work completed.']),$ticket->id);
$assert(DB::table('replies')->where('ticket_id',$ticket->id)->count() === 1, 'Developer reply not saved.');
$denied = function ($action) use ($assert) {
    try { $action(); throw new RuntimeException('Unauthorized ticket mutation accepted.'); }
    catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { $assert($e->getStatusCode() === 403, 'Wrong access rejection.'); }
};
$denied(fn () => $controller->updateDetails(Request::create('/tickets/'.$ticket->id,'PUT',['agent_id'=>8]),$ticket->id));
$denied(fn () => $controller->destroy($ticket->id));
Auth::guard('web')->setUser(User::findOrFail(8));
$denied(fn () => TicketAccess::authorize($ticket));
$denied(fn () => $controller->reply(Request::create('/reply','POST',['message'=>'Unauthorized']),$ticket->id));
$denied(fn () => $controller->changeStatus(Request::create('/status','POST',['ticketId'=>$ticket->id,'status'=>'open'])));
Auth::guard('web')->setUser(User::findOrFail(1));
$controller->updateDetails(Request::create('/details','PUT',['agent_id'=>8]),$ticket->id);
$assert((int) $ticket->fresh()->agent_id === 8 && $ticket->fresh()->status === 'resolved', 'Reassignment cleared unrelated ticket status.');
$assert(Notification::sent(User::findOrFail(8), App\Notifications\SystemNotification::class)->isNotEmpty(), 'New assignee was not notified.');
$controller->updateDetails(Request::create('/details','PUT',['agent_id'=>7]),$ticket->id);
$restrictedManager = new class extends User {
    public function moduleToggleAllows(string $moduleSlug, string $permission = 'view'): bool { return false; }
};
$restrictedManager->forceFill(['id'=>6,'company_id'=>1,'role'=>'manager']);
Auth::guard('web')->setUser($restrictedManager);
$denied(fn () => $controller->updateDetails(Request::create('/details','PUT',['agent_id'=>8]),$ticket->id));
$assert((int) $ticket->fresh()->agent_id === 7, 'Revoked assign permission did not protect the assignment.');
Auth::guard('web')->setUser(User::findOrFail(1));
foreach ([['agent_id'=>3],['requester_name'=>3]] as $bad) {
    try { $controller->store(Request::create('/tickets','POST',array_replace($payload,$bad))); throw new RuntimeException('Cross-company assignment accepted.'); }
    catch (Illuminate\Validation\ValidationException $e) {}
}
DB::table('tickets')->insert(['id'=>99,'company_id'=>2,'requester_id'=>3,'requester_name'=>'Other company','status'=>'open']);
$assert(Ticket::find(99) === null, 'Another company ticket is visible.');
$denied(fn () => $controller->bulkAction(Request::create('/bulk','POST',['tickets'=>[$ticket->id,99],'action'=>'change_status','status'=>'open'])));
$assert($ticket->fresh()->status === 'resolved', 'Rejected bulk request partially changed a ticket.');
try { $controller->changeStatus(Request::create('/status','POST',['ticketId'=>$ticket->id,'status'=>'invalid'])); throw new RuntimeException('Invalid status accepted.'); }
catch (Illuminate\Validation\ValidationException $e) {}
$assert(EnsureModuleAccess::permissionForRequest(Request::create('/status','POST'), 'tickets.change-status') === 'edit', 'Progress uses wrong role permission.');
$assert(EnsureModuleAccess::permissionForRequest(Request::create('/details','PUT',['agent_id'=>8]), 'tickets.updateDetails') === 'edit', 'Ticket editing must retain its edit permission check in addition to assignment authorization.');
$registry=app(App\Services\AdminWorkspaceModules::class);
$assert($registry->featuresForRoute('tickets.change-status') === ['tickets'] && $registry->featuresForRoute('ticket-groups.store') === ['tickets'], 'Ticket mutations escape module entitlement checks.');
$assert($registry->permissionMatrixModules()->contains('slug','tickets'), 'Internal tickets missing from role matrix.');
foreach (['index','create','edit','show'] as $view) token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/tickets/'.$view.'.blade.php'))), TOKEN_PARSE);
echo "PASS: admin creation, developer assignment/progress/replies, persisted activity, private notifications, role/module mapping, bulk atomic authorization and tenant isolation.\n";
