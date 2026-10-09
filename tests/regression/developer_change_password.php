<?php
use Illuminate\Support\Facades\{Auth, DB, Schema, Hash};
require __DIR__.'/developer_task_assignment.php';
Schema::table('users',function($t){$t->text('raw_password')->nullable();$t->boolean('must_change_password')->default(false);});
DB::table('users')->where('id',1)->update(['password'=>Hash::make('OldPassword1!')]);
Auth::guard('web')->setUser(App\Models\User::findOrFail(1));
$controller=new App\Http\Controllers\DeveloperPortalController;
$original=DB::table('users')->where('id',1)->value('password');
foreach(['abcdef1!','ABCDEFG1!','Abcdefg!','Abcdefg1','Ab1!'] as $weak){
 $request->merge(['current_password'=>'OldPassword1!','new_password'=>$weak,'new_password_confirmation'=>$weak]);
 try{$controller->updatePassword($request);throw new RuntimeException('Weak password accepted');}
 catch(Illuminate\Validation\ValidationException $e){if(!isset($e->errors()['new_password']))throw $e;}
 if(DB::table('users')->where('id',1)->value('password')!==$original)throw new RuntimeException('Rejected password modified account');
}
$request->merge(['current_password'=>'OldPassword1!','new_password'=>'ValidPass1!','new_password_confirmation'=>'Different1!']);
try{$controller->updatePassword($request);throw new RuntimeException('Mismatch accepted');}catch(Illuminate\Validation\ValidationException $e){}
$request->merge(['current_password'=>'WrongPassword1!','new_password_confirmation'=>'ValidPass1!']);
$controller->updatePassword($request);
if(DB::table('users')->where('id',1)->value('password')!==$original)throw new RuntimeException('Wrong current password accepted');
$request->merge(['current_password'=>'OldPassword1!']);
$controller->updatePassword($request);
if(!Hash::check('ValidPass1!',DB::table('users')->where('id',1)->value('password')))throw new RuntimeException('Valid password failed');
app('view')->share('errors',new Illuminate\Support\ViewErrorBag);
$html=$controller->settings()->render();
if(!str_contains($html,'one uppercase letter')||!str_contains($html,'setCustomValidity'))throw new RuntimeException('Password form validation missing');
echo "PASS: weak passwords and confirmation mismatch rejected, current password verified, valid password saved and settings form renders validation.\n";
