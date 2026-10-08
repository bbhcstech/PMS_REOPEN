<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
DB::purge('tenant');
Schema::connection('tenant')->create('projects', function ($table) {
    $table->id(); $table->string('name'); $table->timestamps();
});
DB::connection('tenant')->table('projects')->insert(['name' => 'Existing']);
$migration = require dirname(__DIR__, 2) . '/database/migrations/tenant/2026_10_08_140000_add_project_miroboard_flag.php';
$migration->up();
$migration->up();
$project = Project::create(['name' => 'New project', 'enable_miroboard' => false]);
if ($project->id !== 2 || DB::connection('tenant')->table('projects')->where('id', 1)->value('name') !== 'Existing') {
    throw new RuntimeException('Project creation or preservation failed.');
}
$connection = $project->getConnection();
$connection->beginTransaction();
Project::create(['name' => 'Incomplete project', 'enable_miroboard' => true]);
$connection->rollBack();
if ($connection->table('projects')->count() !== 2) throw new RuntimeException('Project transaction rollback failed.');
echo "Project creation flag checks passed.\n";
