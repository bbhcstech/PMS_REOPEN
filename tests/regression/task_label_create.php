<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\TaskLabelController;
use App\Models\TaskLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};

config(['database.default' => 'tenant', 'database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
Schema::create('projects', function ($t) { $t->id(); $t->string('name'); $t->softDeletes(); });
Schema::create('task_label_list', function ($t) { $t->id(); $t->string('label_name'); $t->string('color'); $t->unsignedBigInteger('project_id')->nullable(); $t->string('description')->nullable(); $t->timestamps(); });
DB::table('projects')->insert(['id' => 1, 'name' => 'Website design']);
$controller = new TaskLabelController;
$request = Request::create('/labels', 'POST', ['name' => 'Review', 'color' => '#69D100', 'project_id' => 1, 'description' => 'Ready for review']);
$request->headers->set('Accept', 'application/json');
$response = $controller->store($request);
if ($response->getStatusCode() !== 201 || !$response->getData()->success || $response->getData()->label->project_name !== 'Website design' || TaskLabel::count() !== 1) throw new RuntimeException('Label JSON save failed.');
$request->merge(['name' => 'General', 'project_id' => null]);
if ($controller->store($request)->getData()->label->project_name !== null) throw new RuntimeException('Optional project rejected.');
$request->merge(['name' => '']);
try { $controller->store($request); throw new RuntimeException('Blank label accepted.'); }
catch (Illuminate\Validation\ValidationException $e) {}
if (TaskLabel::count() !== 2) throw new RuntimeException('Rejected label wrote data.');
echo "PASS: label persistence, JSON response, project display, optional project and validation.\n";
