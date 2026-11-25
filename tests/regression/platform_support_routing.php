<?php

require __DIR__.'/internal_tickets.php';

use App\Http\Controllers\Admin\CompanyComplaintController;
use App\Models\Central\{Company, CompanyComplaint};
use App\Models\{Ticket, User};
use App\Services\{ComplaintService, InternalTicketRouting};
use App\Support\PlatformSupportAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};

DB::connection('central')->statement('CREATE TABLE companies (id integer primary key, name text, db_name text, deleted_at text)');
DB::connection('central')->table('companies')->insert(['id'=>1,'name'=>'Alpha','db_name'=>DB::connection('tenant')->getDatabaseName()]);
Schema::connection('central')->create('company_complaints', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('raised_by_id');
    foreach (['ticket_id','raised_by_type','raised_by_name','raised_by_email','subject','description','priority','status','related_module','related_record_id'] as $column) $t->text($column)->nullable();
    $t->timestamps(); $t->softDeletes();
});
Schema::connection('central')->create('complaint_conversations', function ($t) {
    $t->id(); $t->unsignedBigInteger('complaint_id'); $t->unsignedBigInteger('sender_id')->nullable(); $t->string('sender_type'); $t->text('message'); $t->timestamps();
});
Schema::connection('central')->create('complaint_attachments', function ($t) {
    $t->id(); $t->unsignedBigInteger('complaint_id'); $t->unsignedBigInteger('conversation_id')->nullable();
    $t->string('uploaded_by_type'); $t->string('original_name'); $t->string('file_path'); $t->timestamps();
});
DB::table('users')->where('id',1)->update(['email'=>'admin@alpha.test']);
DB::table('users')->where('id',8)->update(['email'=>'staff@alpha.test']);
DB::table('users')->insert(['id'=>9,'company_id'=>1,'name'=>'Client','role'=>'client','email'=>'client@alpha.test']);
$service=new ComplaintService;
$support=new CompanyComplaintController($service);
$company=Company::findOrFail(1);
foreach ([4,5,6,7,8,9] as $staffId) {
    $staff=User::findOrFail($staffId); Auth::guard('web')->setUser($staff);
    $assert(!PlatformSupportAccess::companyAdmin($staff), 'Staff account has platform support authority.');
    $assert(!$staff->moduleToggleAllows('company-complaints') && !$staff->hasModulePermission('company-complaints'), 'Role/module defaults expose Platform Support to staff.');
    $denied(fn () => $support->index(Request::create('/admin/company-complaints')));
    $denied(fn () => $support->create());
    $denied(fn () => $support->store(Request::create('/admin/company-complaints','POST')));
    $denied(fn () => $support->show(1));
    $denied(fn () => $support->messages(Request::create('/messages'),1));
    $denied(fn () => $support->reply(Request::create('/reply','POST',['message'=>'Wrong channel']),1));
    $denied(fn () => $support->reopen(Request::create('/reopen','POST'),1));
    $denied(fn () => $support->downloadAttachment(1,1));
    $denied(fn () => $service->createComplaint(['subject'=>'Wrong channel','description'=>'Staff must use internal tickets'],$company,$staff));
    (new App\Http\Controllers\TicketController)->store(Request::create('/tickets','POST',[
        'requester_type'=>$staff->role==='client'?'client':'employee','requester_name'=>$staff->id,
        'subject'=>'Internal request '.$staffId,'description'=>'Help from the company team',
    ]));
    $assert(Ticket::where('requester_id',$staffId)->where('subject','Internal request '.$staffId)->exists(), 'Company role could not raise its own Internal Ticket.');
    $assert(CompanyComplaint::count()===0, 'Internal request appeared in Platform Support.');
}
Auth::guard('web')->setUser(User::findOrFail(1));
foreach ([1,8] as $owner) {
    DB::connection('central')->table('company_complaints')->insert(['id'=>$owner,'company_id'=>1,'raised_by_id'=>$owner,'raised_by_type'=>'company_admin',
        'raised_by_name'=>$owner===1?'Admin':'Employee','raised_by_email'=>$owner===1?'admin@alpha.test':'staff@alpha.test',
        'ticket_id'=>'CMP-'.$owner,'subject'=>'Support '.$owner,'description'=>'Original request '.$owner,'priority'=>'HIGH','status'=>'WAITING FOR COMPANY','created_at'=>now(),'updated_at'=>now()]);
}
DB::connection('central')->table('company_complaints')->insert(['id'=>99,'company_id'=>2,'raised_by_id'=>8,'subject'=>'Other company','status'=>'OPEN']);
DB::connection('central')->table('complaint_conversations')->insert([
    ['complaint_id'=>8,'sender_id'=>8,'sender_type'=>'company_admin','message'=>'Employee follow-up'],
    ['complaint_id'=>8,'sender_id'=>1,'sender_type'=>'super_admin','message'=>'Private platform conversation'],
]);
DB::connection('central')->table('complaint_attachments')->insert([
    ['complaint_id'=>8,'uploaded_by_type'=>'company_admin','original_name'=>'staff.png','file_path'=>'admin/uploads/complaints/staff.png'],
    ['complaint_id'=>8,'uploaded_by_type'=>'super_admin','original_name'=>'private.png','file_path'=>'admin/uploads/complaints/private.png'],
]);
$router=new InternalTicketRouting;
$before=Ticket::count();
$assert($router->reconcile($company)===1, 'Legacy staff request was not moved into Internal Tickets.');
$assert(Ticket::count()===$before+1 && CompanyComplaint::find(8)===null && CompanyComplaint::find(1)!==null && CompanyComplaint::find(99)!==null, 'Repair changed admin or other company platform requests.');
$source=CompanyComplaint::withTrashed()->findOrFail(8);
$internal=Ticket::findOrFail($source->related_record_id);
$assert($internal->requester_id==8 && $internal->status==='pending' && $internal->description==='Original request 8', 'Transferred ticket lost its requester, content or progress.');
$assert($internal->replies()->count()===2 && $internal->replies()->first()->message==='Employee follow-up', 'Staff history missing or private platform replies leaked.');
$assert($internal->replies()->where('attachment','admin/uploads/complaints/staff.png')->exists()
    && !$internal->replies()->where('attachment','admin/uploads/complaints/private.png')->exists(), 'Staff attachment missing or platform attachment exposed.');
$assert($source->trashed() && $source->conversations()->count()===2, 'Original platform conversation was destroyed.');
$assert($router->reconcile($company)===0 && Ticket::count()===$before+1, 'Repair duplicated internal tickets on refresh.');
DB::connection('central')->table('company_complaints')->where('id',8)->update(['deleted_at'=>null]);
$assert($router->reconcile($company)===1 && Ticket::count()===$before+1, 'Retry after a central commit failure duplicated the tenant ticket.');
Auth::guard('web')->setUser(User::findOrFail(8));
App\Support\TicketAccess::authorize($internal);
$denied(fn () => $service->addResponse(CompanyComplaint::findOrFail(1),'Wrong channel',Auth::user(),'company_admin'));
echo "PASS: admin-only Platform Support, staff navigation/service/URL denial, internal ticket routing, legacy repair, retry safety, privacy and company isolation.\n";
