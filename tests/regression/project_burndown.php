<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ProjectController;
use App\Services\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
app(CompanyContext::class)->reset((object) ['id' => 1]);
$db = DB::connection('tenant');
$schema = $db->getSchemaBuilder();
$schema->create('projects', function ($table) {
    $table->id();
    $table->unsignedInteger('company_id');
    $table->softDeletes();
});
$schema->create('tasks', function ($table) {
    $table->id();
    $table->unsignedInteger('company_id')->nullable();
    $table->unsignedInteger('project_id');
    $table->string('status');
    $table->timestamp('updated_at')->nullable();
});

$db->table('projects')->insert(['id' => 1, 'company_id' => 1]);
$db->table('tasks')->insert([
    ['company_id' => 1, 'project_id' => 1, 'status' => 'Completed', 'updated_at' => '2026-11-15 23:59:59'],
    ['company_id' => 1, 'project_id' => 1, 'status' => 'Completed', 'updated_at' => '2026-11-16 23:59:59'],
    ['company_id' => null, 'project_id' => 1, 'status' => 'Completed', 'updated_at' => '2026-11-18 00:00:00'],
    ['company_id' => 1, 'project_id' => 1, 'status' => 'To Do', 'updated_at' => '2026-11-16 12:00:00'],
    ['company_id' => 1, 'project_id' => 1, 'status' => 'Completed', 'updated_at' => null],
    ['company_id' => 1, 'project_id' => 1, 'status' => 'Completed', 'updated_at' => '2026-11-20 12:00:00'],
    ['company_id' => 2, 'project_id' => 1, 'status' => 'Completed', 'updated_at' => '2026-11-16 12:00:00'],
    ['company_id' => 1, 'project_id' => 2, 'status' => 'Completed', 'updated_at' => '2026-11-16 12:00:00'],
]);

$db->enableQueryLog();
$view = (new ProjectController)->burndown(Request::create('/projects/1/burndown', 'GET', [
    'start_date' => '2026-11-16',
    'end_date' => '2026-11-18',
]), 1);
$data = $view->getData();
if ($data['labels'] !== ['16-11-2026', '17-11-2026', '18-11-2026', '19-11-2026']
    || $data['actual'] !== [4, 4, 3, 3]) {
    throw new RuntimeException('Burndown dates, scoped cumulative totals, or legacy date boundaries changed.');
}
$completionQueries = array_filter($db->getQueryLog(), fn ($entry) => in_array('Completed', $entry['bindings'], true));
if (count($completionQueries) !== 1) {
    throw new RuntimeException('Burndown should load completion totals with a single query.');
}

echo "PASS: original burndown date boundaries, cumulative totals, tenant isolation, and a single completion query.\n";
