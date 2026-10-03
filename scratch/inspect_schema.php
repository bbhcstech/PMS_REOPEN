<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = ['tasks', 'projects', 'attendances', 'task_timers', 'time_logs', 'tickets'];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        $cols = DB::select("SHOW COLUMNS FROM `{$table}`");
        $names = array_map(fn($c) => $c->Field, $cols);
        echo "Table {$table}: " . implode(', ', $names) . "\n";
    }
}
