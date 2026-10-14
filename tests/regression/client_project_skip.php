<?php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

foreach (['tenant', 'central'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
foreach ([App\Models\User::class, App\Models\Client::class, App\Models\Project::class, App\Models\Deal::class, App\Models\DealStage::class, App\Models\ProjectCategory::class, App\Models\Currency::class, App\Models\ProjectActivity::class, App\Models\UserActivity::class, App\Models\ProjectUpdate::class] as $class) {
    $model = new $class;
    $columns = array_unique(array_merge($model->getFillable(), array_keys($model->getAttributes()), ['deleted_at', 'archived_at']));
    Schema::create($model->getTable(), function ($table) use ($columns) {
        $table->id();
        foreach ($columns as $column) if (! in_array($column, ['id', 'created_at', 'updated_at'], true)) $table->text($column)->nullable();
        $table->timestamps();
    });
}
$actor = new App\Models\User;
$actor->forceFill(['name' => 'HR', 'email' => 'hr@example.test', 'role' => 'hr'])->save();
Illuminate\Support\Facades\Auth::guard('web')->setUser($actor);
$controller = new ClientController;
$base = ['name' => 'Test Client', 'email' => 'client@example.test', 'password' => 'Valid@12345', 'mobile' => '9876543210', 'country' => 'India', 'deal_submit_mode' => 'later'];
$requestFor = function ($data) {
    $request = Request::create('/clients', 'POST', $data);
    $request->setLaravelSession(app('session')->driver());
    app()->instance('request', $request);
    return $request;
};
function checkSkip($ok, $message) { if (! $ok) throw new RuntimeException($message); }
foreach (['', '   '] as $blank) {
    try { $controller->store($requestFor($base + ['project_submit_mode' => 'final', 'project_name' => $blank])); throw new RuntimeException('Save and Continue accepted a blank project name.'); }
    catch (ValidationException $e) { checkSkip(isset($e->errors()['project_name']), 'Missing name did not produce project error.'); }
}
checkSkip(DB::table('clients')->count() === 0, 'Validation created a partial client.');
$controller->store($requestFor($base + ['project_submit_mode' => 'later', 'project_name' => 'Do not create', 'project_deadline' => 'invalid', 'project_employee_ids' => 'invalid']));
checkSkip(DB::table('clients')->count() === 1 && DB::table('projects')->count() === 0, 'Skipping project created a project or blocked client creation.');
$withDeal = array_merge($base, ['email' => 'dealclient@example.test', 'project_submit_mode' => 'later', 'project_name' => 'Still skip', 'deal_submit_mode' => 'final', 'deal_name' => 'First deal']);
$controller->store($requestFor($withDeal));
checkSkip(DB::table('clients')->count() === 2 && DB::table('projects')->count() === 0 && DB::table('deals')->count() === 1, 'Add Deals failed when the project was skipped.');
$withProject = array_merge($base, ['email' => 'projectclient@example.test', 'project_submit_mode' => 'final', 'project_name' => 'Named project']);
$controller->store($requestFor($withProject));
checkSkip(DB::table('projects')->where('name', 'Named project')->count() === 1 && DB::table('clients')->count() === 3, 'Save and Continue did not save a named project.');
$compiled = app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/clients/create.blade.php')));
token_get_all($compiled, TOKEN_PARSE);
echo "Client project skip, required name, deal continuation and template checks passed.\n";
