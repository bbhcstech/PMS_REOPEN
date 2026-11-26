<?php
require __DIR__.'/developer_task_assignment.php';
use Illuminate\Support\Facades\DB;
$records=app(App\Services\DeveloperAssignmentHistory::class)->records();
if($records->isEmpty())throw new RuntimeException('Assignments missing from history');
$first=$records->firstWhere('title','pms');
if(!$first||$first->developer_name!=='Developer'||$first->company_name!=='Company')throw new RuntimeException('Developer/company resolution failed');
DB::table('users')->insert(['id'=>2,'name'=>'Second Developer','email'=>'second@example.test','role'=>'developer']);
DB::table('assigned_task_user')->insert(['task_id'=>$first->id,'user_id'=>2,'assigned_by'=>1,'assigned_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
DB::table('tasks')->where('id',$first->id)->update(['status'=>'completed','deleted_at'=>now()]);
$records=app(App\Services\DeveloperAssignmentHistory::class)->records();$first=$records->firstWhere('title','pms');
if(!str_contains($first->developer_name,'Second Developer')||$first->status!=='completed'||$first->assigner_name!=='Developer')throw new RuntimeException('Multiple developers/completed history lost');
for($i=0;$i<55;$i++)DB::table('tasks')->insert(['title'=>'History '.$i,'assigned_to'=>1,'created_by'=>1,'company_id'=>1,'status'=>'assigned','created_at'=>now(),'updated_at'=>now()]);
if(app(App\Services\DeveloperAssignmentHistory::class)->records()->count()<56)throw new RuntimeException('History truncated');
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/superadmin/developers/index.blade.php'))),TOKEN_PARSE);
echo "PASS: real assignments, central company names with minimal tenant schema, multiple developers, completed/archived history, more than 50 records and Blade compilation.\n";
