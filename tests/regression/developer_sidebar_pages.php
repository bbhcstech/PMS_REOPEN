<?php
use Illuminate\Support\Facades\{Auth, DB, Schema};
require __DIR__.'/developer_task_assignment.php';
Auth::guard('web')->setUser(App\Models\User::findOrFail(1));
app('view')->share('errors', new Illuminate\Support\ViewErrorBag);
$controller = new App\Http\Controllers\DeveloperPortalController;
$request = Illuminate\Http\Request::create('/developer','GET');
foreach (['myContributions','deadlines','notifications','profile'] as $method) {
    try {
        $view = $controller->$method($request);
        if (strlen($view->render()) < 100) throw new RuntimeException('Empty page');
        echo "PASS: {$method} renders.\n";
    } catch (Throwable $e) { fwrite(STDERR, "FAIL: {$method}: ".$e->getMessage()."\n"); exit(1); }
}
DB::table('tasks')->update(['status'=>'completed']);
Schema::table('tasks',fn($t)=>$t->dropColumn('deleted_at'));
Schema::table('users',fn($t)=>$t->dropColumn('personal_email'));
foreach (['myContributions','deadlines','notifications','profile'] as $method) {
    try { $controller->$method($request)->render(); echo "PASS: {$method} legacy schema renders.\n"; }
    catch (Throwable $e) { fwrite(STDERR, "FAIL: {$method}: ".$e->getMessage()."\n"); exit(1); }
}

Schema::table('companies',fn($t)=>$t->string('name')->nullable());
DB::table('companies')->where('id',1)->update(['name'=>'Real company']);
DB::table('tasks')->insert(['title'=>'Another developer private task','assigned_to'=>999,'company_id'=>1,'project_id'=>1,'status'=>'completed','created_at'=>now(),'updated_at'=>now()]);
foreach (['myContributions','deadlines','notifications','profile'] as $method) {
    try {
        $html = $controller->$method($request)->render();
        if (str_contains($html,'Another developer private task')) throw new RuntimeException('Other developer task leaked');
        if ($method !== 'deadlines' && !str_contains($html,'pms')) throw new RuntimeException('Real developer task missing');
        echo "PASS: {$method} populated metadata and developer isolation.\n";
    } catch (Throwable $e) { fwrite(STDERR,"FAIL: ".$e->getMessage()."\n"); exit(1); }
}
