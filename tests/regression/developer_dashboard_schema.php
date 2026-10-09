<?php
use Illuminate\Support\Facades\{Auth, DB, Schema};
require __DIR__.'/developer_task_assignment.php';
$developer=App\Models\User::findOrFail(1);Auth::guard('web')->setUser($developer);
$controller=new App\Http\Controllers\DeveloperPortalController;
$view=$controller->dashboard();
app('view')->share('errors',new Illuminate\Support\ViewErrorBag);
if(!str_contains($view->render(),'Dashboard Overview'))throw new RuntimeException('Dashboard HTML render failed');
if($view->getData()['kpis']['total_assigned']<1)throw new RuntimeException('Assigned tasks missing');
Schema::table('users',fn($t)=>$t->dropColumn('personal_email'));
Schema::table('tasks',fn($t)=>$t->dropColumn('deleted_at'));
$view=$controller->dashboard();
if($view->getData()['kpis']['total_assigned']<1)throw new RuntimeException('Legacy schema lost assignments');
Schema::drop('tasks');
$view=$controller->dashboard();
if($view->getData()['kpis']['total_assigned']!==0)throw new RuntimeException('Empty tenant dashboard failed');
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/developer/dashboard.blade.php'))),TOKEN_PARSE);
echo "PASS: Developer Dashboard loads real tasks, minimal company schema, optional email/deletion columns and tenants without tasks.\n";
