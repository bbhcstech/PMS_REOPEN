<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProjectCodeSchemaRepairTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'project_code_test',
            'database.connections.project_code_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);
    }

    public function test_repairs_missing_column_without_changing_existing_projects(): void
    {
        foreach ($this->migrationPaths() as $path) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();
                $table->string('name');
            });
            DB::table('projects')->insert(['name' => 'Existing project']);

            $migration = require $path;
            $migration->up();
            $migration->up();

            $this->assertTrue(Schema::hasColumn('projects', 'project_code'));
            $this->assertSame('Existing project', DB::table('projects')->value('name'));
            $this->assertNull(DB::table('projects')->value('project_code'));

            DB::table('projects')->insert(['name' => 'Client project', 'project_code' => 'bit26-27/0001']);
            $this->assertSame('bit26-27/0001', DB::table('projects')
                ->where('project_code', 'like', 'bit26-27/%')
                ->orderByDesc('id')->value('project_code'));

            Schema::drop('projects');
        }
    }

    public function test_preserves_existing_codes_and_column_definition(): void
    {
        foreach ($this->migrationPaths() as $path) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();
                $table->string('project_code', 255)->unique();
            });
            DB::table('projects')->insert(['project_code' => 'MANUAL-001']);
            $columns = Schema::getColumns('projects');

            $migration = require $path;
            $migration->up();
            $migration->down();

            $this->assertSame($columns, Schema::getColumns('projects'));
            $this->assertSame('MANUAL-001', DB::table('projects')->value('project_code'));
            Schema::drop('projects');
        }
    }

    public function test_safely_skips_databases_without_projects(): void
    {
        foreach ($this->migrationPaths() as $path) {
            (require $path)->up();
            $this->assertFalse(Schema::hasTable('projects'));
        }
    }

    private function migrationPaths(): array
    {
        return [
            database_path('migrations/2026_10_07_000004_add_project_code_to_projects_table.php'),
            database_path('migrations/tenant/2026_10_07_000004_add_project_code_to_projects_table.php'),
        ];
    }
}
