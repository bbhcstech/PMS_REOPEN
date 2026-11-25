<?php
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
require __DIR__.'/developer_task_assignment.php';
$rule=['required','string','max:128',Password::min(8)->mixedCase()->numbers()->symbols()];
for($i=0;$i<300;$i++){
 $password=App\Support\DeveloperPassword::generate();
 if(strlen($password)!==10||Validator::make(['password'=>$password],['password'=>$rule])->fails())throw new RuntimeException('Generated password violates policy');
}
foreach(['uVGgSkvZks','Abcdefg!','ABCDEFG1!','abcdefg1!','Ab1!','Abcdefg1']as $weak)if(!Validator::make(['password'=>$weak],['password'=>$rule])->fails())throw new RuntimeException('Weak password accepted');
if(Validator::make(['password'=>'Developer@123'],['password'=>$rule])->fails())throw new RuntimeException('Valid password rejected');
$request->merge(['password'=>'uVGgSkvZks']);
try{(new App\Http\Controllers\SuperAdminController)->resetDeveloperPassword($request,1);throw new RuntimeException('Controller accepted weak custom password');}catch(Illuminate\Validation\ValidationException $e){if(!isset($e->errors()['password']))throw $e;}
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/superadmin/developers/index.blade.php'))),TOKEN_PARSE);
echo "PASS: 300 generated passwords meet policy, weak custom passwords rejected by controller, valid password accepted and Blade compilation.\n";
