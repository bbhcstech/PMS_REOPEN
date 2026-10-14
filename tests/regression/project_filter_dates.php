<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
auth()->forgetGuards();
$queries = [];
// Stop at the first project query: verify controller validation and the real
// filter SQL without touching any existing company database or data.
class ProjectFilterQueryReached extends RuntimeException {}
DB::connection('tenant')->beforeExecuting(function ($sql, $bindings) use (&$queries) {
    $queries[] = [$sql, $bindings];
    throw new ProjectFilterQueryReached;
});
$controller = new ProjectController;
foreach ([
    ['start_date' => '2026-10-06', 'end_date' => '2026-10-05'],
    ['start_date' => 'invalid', 'end_date' => '2026-10-06'],
    ['start_date' => '2026-10-06', 'end_date' => 'invalid'],
] as $filters) {
    try {
        $controller->index(Request::create('/projects', 'GET', $filters));
        throw new RuntimeException('Invalid dates were accepted.');
    } catch (ValidationException $e) {
        $field = $filters['start_date'] === 'invalid' ? 'start_date' : 'end_date';
        if (! isset($e->errors()[$field])) throw new RuntimeException('Missing date validation error.');
    }
}
if ($queries) throw new RuntimeException('Invalid dates reached the database.');
foreach ([
    ['start_date' => '2026-10-06', 'end_date' => '2026-10-06'],
    ['start_date' => '2026-10-06', 'end_date' => '2026-10-07'],
    ['start_date' => '2026-10-06'],
    ['end_date' => '2026-10-06'],
    [],
    ['start_date' => '', 'end_date' => ''],
] as $filters) {
    try {
        $controller->index(Request::create('/projects', 'GET', $filters));
        throw new RuntimeException('Expected project query was not reached.');
    } catch (ProjectFilterQueryReached $e) {
        [$sql, $bindings] = end($queries);
        foreach ($filters as $field => $value) {
            if ($value !== '' && ! in_array($value, $bindings, true)) throw new RuntimeException('Date filter binding changed.');
        }
    }
}
echo "PASS: earlier end dates and malformed dates rejected before queries; equal/later dates and optional filters accepted.\n";
