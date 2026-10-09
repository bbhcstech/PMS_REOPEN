<?php
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
require __DIR__.'/developer_task_assignment.php';
$before=DB::table('tasks')->count();
$request->merge(['start_date'=>'2026-10-12','due_date'=>'2026-10-11']);
try{(new App\Http\Controllers\SuperAdminController)->assignWork($request);throw new RuntimeException('Earlier deadline accepted');}catch(ValidationException $e){if(!isset($e->errors()['due_date']))throw $e;}
if(DB::table('tasks')->count()!==$before)throw new RuntimeException('Invalid assignment persisted');
$request->merge(['start_date'=>'2026-10-12','due_date'=>'2026-10-12']);
(new App\Http\Controllers\SuperAdminController)->assignWork($request);
if(DB::table('tasks')->count()!==$before+1)throw new RuntimeException('Same-day deadline rejected');
echo "PASS: earlier deadline rejected before persistence and same-day assignment accepted.\n";
