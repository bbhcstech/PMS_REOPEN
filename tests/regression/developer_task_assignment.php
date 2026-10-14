<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB, Schema, Auth, Mail};
use Illuminate\Http\Request;
app()->instance('db.connector.sqlite', new class implements Illuminate\Database\Connectors\ConnectorInterface {
    private array $databases = [];
    public function connect(array $config) { return $this->databases[$config['database']] ??= new PDO('sqlite::memory:'); }
});
foreach (['central', 'tenant'] as $connection) {
    config(["database.connections.$connection" => ['driver'=>'sqlite','database'=>$connection,'prefix'=>'']]); DB::purge($connection);
}
config(['database.default'=>'tenant','session.driver'=>'array','queue.default'=>'sync','mail.default'=>'array']);
(require database_path('migrations/central/2026_08_08_000001_create_central_companies_table.php'))->up();
Schema::connection('central')->create('audit_logs', function ($t) {
    $t->id(); $t->integer('user_id')->nullable(); $t->integer('company_id')->nullable();
    foreach (['action','entity_type','entity_id','new_values','ip_address','user_agent'] as $column) $t->text($column)->nullable();
    $t->timestamps();
});
Schema::create('users', function ($t) {
    $t->id(); foreach (['name','email','role','password','personal_email'] as $column) $t->string($column)->nullable(); $t->integer('company_id')->nullable(); $t->timestamps();
});
Schema::create('companies', fn ($t) => $t->id());
Schema::create('projects', function ($t) { $t->id(); $t->integer('company_id'); $t->string('name'); $t->timestamps(); });
Schema::create('tasks', function ($t) {
    $t->id(); foreach (['company_id','project_id','assigned_to','created_by'] as $column) $t->integer($column)->nullable();
    foreach (['title','description','additional_instructions','attachments','start_date','due_date','status'] as $column) $t->text($column)->nullable();
    $t->timestamps();
});
Schema::create('assigned_task_user', function ($t) {
    foreach (['task_id','user_id','assigned_by'] as $column) $t->integer($column)->nullable(); $t->timestamp('assigned_at'); $t->timestamps();
});
DB::table('companies')->insert(['id'=>1]);
App\Models\Company::create(['id'=>1,'name'=>'Company','email'=>'company@example.test','db_name'=>'']);
DB::connection('central')->table('companies')->update(['db_name'=>'']);
DB::table('users')->insert(['id'=>1,'name'=>'Developer','email'=>'dev@example.test','personal_email'=>'dev@example.test','role'=>'developer','password'=>'unchanged','company_id'=>1]);
DB::table('projects')->insert(['id'=>1,'company_id'=>1,'name'=>'Test Project']);
$admin = new App\Models\User(['role'=>'superadmin']); $admin->id=99; Auth::guard('web')->setUser($admin);
Mail::fake();
$request = Request::create('/super-admin/developers/assign-work','POST',[
    'developer_id'=>1,'developer_email'=>'dev@example.test','task_title'=>'pms',
    'description'=>'Task description','additional_instructions'=>'Instructions','attachments'=>'example.com',
    'company_id'=>1,'project_id'=>1,'priority'=>'medium','estimate_hours'=>8,
    'start_date'=>'2026-10-09','due_date'=>'2026-10-12',
]);
$request->setLaravelSession(app('session')->driver()); app()->instance('request',$request);
$response = (new App\Http\Controllers\SuperAdminController)->assignWork($request);
$task = DB::table('tasks')->first();
if ($response->getStatusCode()!==302 || !$task || $task->assigned_to!=1 || $task->project_id!=1 || $task->estimate_hours!=8 || $task->priority!=='medium' || $task->deleted_at!==null) throw new RuntimeException('Task assignment failed.');
if (DB::table('users')->count()!==1 || DB::table('users')->value('password')!=='unchanged') throw new RuntimeException('Existing account was changed.');
if (DB::table('assigned_task_user')->count()!==1) throw new RuntimeException('Assignment pivot missing.');
App\Services\DeveloperTaskSchema::ensure();
if (Mail::sent(App\Mail\WorkAssignedNotification::class)->count()!==1 || Mail::sent(App\Mail\DeveloperAccountCreated::class)->count()!==0) throw new RuntimeException('Incorrect notification behavior.');
DB::connection('central')->table('companies')->update(['db_name'=>'unavailable_company']);
(new App\Http\Controllers\SuperAdminController)->assignWork($request);
if (config('database.connections.tenant.database')!=='tenant' || DB::table('tasks')->count()!==2) throw new RuntimeException('Tenant sync did not restore the assignment database.');
echo "PASS: legacy schema repair, task fields/assignment, selected project, existing account/password preservation, notification and repeat schema repair.\n";
