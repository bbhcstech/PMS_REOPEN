<?php

// Run with: php tests/regression/employee_dashboard_charts.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\EmployeeDashboardCharts;
use Illuminate\Support\Facades\DB;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
$db = DB::connection('tenant');
foreach ([
    'tasks' => 'id integer primary key, company_id integer, assigned_to text, status text, deleted_at text',
    'projects' => 'id integer primary key, company_id integer, status text, deleted_at text',
    'tickets' => 'id integer primary key, company_id integer, agent_id integer, requester_id integer, status text',
    'assigned_task_user' => 'task_id integer, user_id integer',
    'project_user' => 'project_id integer, user_id integer',
] as $table => $columns) $db->statement("CREATE TABLE {$table} ({$columns})");
$user = new User(['company_id' => 1]); $user->id = 2;
$db->table('tasks')->insert([
    ['id' => 1, 'company_id' => 1, 'assigned_to' => '2,3', 'status' => 'pending', 'deleted_at' => null],
    ['id' => 2, 'company_id' => 1, 'assigned_to' => '22', 'status' => 'completed', 'deleted_at' => null],
    ['id' => 3, 'company_id' => 2, 'assigned_to' => '2', 'status' => 'pending', 'deleted_at' => null],
    ['id' => 4, 'company_id' => 1, 'assigned_to' => '2', 'status' => 'pending', 'deleted_at' => '2026-01-01'],
    ['id' => 5, 'company_id' => 1, 'assigned_to' => '2', 'status' => 'Completed', 'deleted_at' => null],
]);
$db->table('assigned_task_user')->insert([['task_id' => 1, 'user_id' => 2], ['task_id' => 5, 'user_id' => 2]]);
$db->table('projects')->insert([
    ['id' => 1, 'company_id' => 1, 'status' => 'in_progress', 'deleted_at' => null],
    ['id' => 2, 'company_id' => 2, 'status' => 'completed', 'deleted_at' => null],
]);
$db->table('project_user')->insert([['project_id' => 1, 'user_id' => 2], ['project_id' => 1, 'user_id' => 2], ['project_id' => 2, 'user_id' => 2]]);
for ($i = 1; $i <= 15; $i++) $db->table('tickets')->insert(['id' => $i, 'company_id' => 1, 'agent_id' => 2, 'requester_id' => 2, 'status' => $i <= 12 ? 'resolved' : 'open']);
$db->table('tickets')->insert(['id' => 16, 'company_id' => 2, 'agent_id' => 2, 'requester_id' => 2, 'status' => 'open']);
$charts = (new EmployeeDashboardCharts)->forUser($user);
if ($charts['tasks'] !== ['Completed' => 1, 'Pending' => 1]) throw new RuntimeException('Task counts leaked, duplicated or included deleted records.');
if ($charts['projects'] !== ['In Progress' => 1]) throw new RuntimeException('Project counts leaked or duplicated.');
if ($charts['tickets'] !== ['Open' => 3, 'Resolved' => 12]) throw new RuntimeException('Ticket counts were limited or leaked.');
$html = app('blade.compiler')->compileString(file_get_contents(resource_path('views/partials/dashboard-status-chart.blade.php')));
token_get_all($html, TOKEN_PARSE);
$rendered = Illuminate\Support\Facades\Blade::render(file_get_contents(resource_path('views/partials/dashboard-status-chart.blade.php')), ['counts' => $charts['tickets'], 'subject' => 'tickets']);
if (!str_contains($rendered, 'conic-gradient') || !str_contains($rendered, '80%')) throw new RuntimeException('Real chart or percentage missing.');
$empty = Illuminate\Support\Facades\Blade::render(file_get_contents(resource_path('views/partials/dashboard-status-chart.blade.php')), ['counts' => [], 'subject' => 'tasks']);
if (!str_contains($empty, 'No tasks yet') || str_contains($empty, 'conic-gradient')) throw new RuntimeException('Empty chart invents data.');
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/employee-dashboard.blade.php'))), TOKEN_PARSE);
echo "Dashboard chart checks passed: real status counts, complete ticket totals, assignment and company isolation, deletion filtering, percentages and empty states.\n";
