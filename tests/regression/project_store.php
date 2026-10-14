<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'database.default' => 'tenant', 'session.driver' => 'array']);
DB::purge('tenant');
Schema::create('users', function ($t) { $t->id(); $t->string('name'); $t->string('email'); $t->string('role'); $t->unsignedBigInteger('company_id'); });
Schema::create('clients', function ($t) { $t->id(); });
Schema::create('departments', function ($t) { $t->id(); $t->string('dpt_name'); });
Schema::create('currencies', function ($t) { $t->id(); });
Schema::create('project_categories', function ($t) { $t->id(); });
Schema::create('projects', function ($t) {
    $t->id(); $t->unsignedBigInteger('client_id'); $t->unsignedBigInteger('company_id');
    $t->string('name'); $t->string('project_code'); $t->string('project_type');
    $t->text('description')->nullable(); $t->date('start_date')->nullable(); $t->date('deadline')->nullable();
    $t->string('status')->default('pending'); $t->timestamps(); $t->softDeletes();
});
Schema::create('project_user', function ($t) {
    $t->id(); $t->unsignedBigInteger('project_id'); $t->unsignedBigInteger('user_id');
    $t->decimal('hourly_rate')->default(0); $t->string('role'); $t->timestamps();
});
Schema::create('department_project', function ($t) { $t->id(); $t->unsignedBigInteger('project_id'); $t->unsignedBigInteger('department_id'); $t->timestamps(); });
Schema::create('project_activity', function ($t) { $t->id(); $t->unsignedBigInteger('project_id'); $t->text('activity'); $t->timestamps(); });
Schema::create('user_activities', function ($t) { $t->id(); $t->unsignedBigInteger('company_id'); $t->unsignedBigInteger('user_id'); $t->text('activity'); $t->timestamps(); });
foreach (['2026_10_08_000002_repair_client_project_columns', '2026_06_19_000002_add_project_assignment_tracking_fields', '2026_10_08_140000_add_project_miroboard_flag', '2026_10_08_150000_allow_internal_projects_without_client'] as $migration) {
    (require database_path('migrations/tenant/' . $migration . '.php'))->up();
}
DB::table('users')->insert(['id' => 1, 'name' => 'Admin', 'email' => 'admin@example.test', 'role' => 'admin', 'company_id' => 1]);
DB::table('clients')->insert(['id' => 1]);
DB::table('departments')->insert(['id' => 1, 'dpt_name' => 'IT']);
DB::table('project_categories')->insert(['id' => 1]);
Auth::guard('web')->setUser(User::findOrFail(1));
app(\App\Services\CompanyContext::class)->reset((object) ['id' => 1]);
$app['session']->start();
$controller = new ProjectController;
foreach (['client', 'home'] as $type) {
    $request = Request::create('/projects', 'POST', [
        'name' => ucfirst($type) . ' project', 'project_type' => $type, 'client_id' => 1,
        'category_id' => 1, 'employee_ids' => [1], 'department_ids' => [1],
        'without_deadline' => 1, 'shortcode_option' => 'auto',
    ]);
    $request->setLaravelSession($app['session.store']);
    $response = $controller->store($request);
    if ($response->getTargetUrl() !== route('projects.index')) {
        throw new RuntimeException('Project save failed: ' . json_encode($app['session.store']->get('errors')?->all()));
    }
}
foreach (['projects', 'project_user', 'department_project', 'project_activity', 'user_activities', 'project_updates'] as $table) {
    if (DB::table($table)->count() !== 2) throw new RuntimeException('Missing saved records in ' . $table);
}
echo "Full project store checks passed for client and home projects.\n";
