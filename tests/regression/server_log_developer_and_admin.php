<?php

require __DIR__.'/developer_dashboard_schema.php';

use App\Models\User;
use Illuminate\Support\Facades\{Auth, DB, Schema};

// Match the MySQL lead_contacts.added_by -> users.id restriction from the supplied log.
foreach (['is_active','login_allowed'] as $column) if (!Schema::hasColumn('users',$column)) Schema::table('users',fn ($t) => $t->boolean($column)->default(true));
if (!Schema::hasColumn('users','archived_at')) Schema::table('users',fn ($t) => $t->timestamp('archived_at')->nullable());
DB::statement('PRAGMA foreign_keys = ON');
Schema::create('lead_contacts',function($t){$t->id();$t->unsignedBigInteger('added_by');$t->string('name');$t->foreign('added_by')->references('id')->on('users');});
foreach ([2,3] as $id) DB::table('users')->insert(['id'=>$id,'company_id'=>1,'name'=>'Admin '.$id,'email'=>'admin'.$id.'@example.test','role'=>'admin']);
DB::table('lead_contacts')->insert(['added_by'=>2,'name'=>'Existing lead']);
$platform=new User(['role'=>'superadmin']); $platform->id=99; Auth::guard('web')->setUser($platform);
$controller=new App\Http\Controllers\SuperAdminController;
$result=$controller->deleteAdmin(User::findOrFail(2));
$preserved=User::findOrFail(2);
if ($result->getStatusCode()!==302 || !$preserved->archived_at || $preserved->is_active || $preserved->login_allowed) throw new RuntimeException('Referenced admin was not safely archived and disabled.');
if (DB::table('lead_contacts')->value('added_by')!=2 || DB::table('lead_contacts')->value('name')!=='Existing lead') throw new RuntimeException('Referenced lead was changed.');
if (!DB::connection('central')->table('audit_logs')->where('action','company_admin.archived')->exists()) throw new RuntimeException('Preserved-account action was not audited.');
$controller->deleteAdmin(User::findOrFail(3));
if (User::find(3)!==null) throw new RuntimeException('Unreferenced admin deletion behavior changed.');
Auth::guard('web')->setUser($developer);
try {$controller->deleteAdmin($preserved);throw new RuntimeException('Developer deleted an admin.');}
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {if($e->getStatusCode()!==403)throw $e;}
catch (Illuminate\Http\Exceptions\HttpResponseException $e) {if($e->getResponse()->getTargetUrl()!==route('developer.dashboard'))throw $e;}
echo "PASS: supplied-log dashboard schema failure, restricted lead foreign key, archived/disabled account, preserved attribution, audit and unchanged unreferenced deletion/authorization.\n";
