<?php
require 'vendor/autoload.php';
$app=require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.tenant'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>''],'database.default'=>'tenant']);
Illuminate\Support\Facades\DB::purge('tenant');
Illuminate\Support\Facades\DB::statement('CREATE TABLE projects (id integer primary key, name text, category_id integer)');
Illuminate\Support\Facades\DB::table('projects')->insert(['id'=>1,'name'=>'Existing','category_id'=>7]);
$m=require 'database/migrations/tenant/2026_10_08_000002_repair_client_project_columns.php';
$m->up(); $m->up();
Illuminate\Support\Facades\DB::table('projects')->insert(['id'=>2,'name'=>'New','category_id'=>1,'department_id'=>1,'currency_id'=>4,'without_deadline'=>0,'project_budget'=>null,'hours_allocated'=>null,'completion_percent'=>0,'notes'=>'Test','public_gantt_chart'=>'enable','public_taskboard'=>'enable','client_access'=>1,'need_approval_by_admin'=>0,'calculate_task_progress'=>'true','public'=>0,'allow_client_notification'=>0,'manual_timelog'=>0]);
$m->down();
if (Illuminate\Support\Facades\DB::table('projects')->where('id',1)->value('category_id') !== 7) throw new RuntimeException('Existing project modified');
echo 'PASS: client project fields insert, repeat migration, existing data preservation and safe rollback'.PHP_EOL;
