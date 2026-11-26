<?php
require 'vendor/autoload.php';
$app=require 'bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$files=[];$original=config('database.connections.tenant');
try {
for($i=0;$i<2;$i++){
 $file=tempnam(sys_get_temp_dir(),'pms_growth_');$files[]=$file;
 config(['database.connections.growth_fixture'=>['driver'=>'sqlite','database'=>$file,'prefix'=>'']]);DB::purge('growth_fixture');
 DB::connection('growth_fixture')->statement('CREATE TABLE users (id INTEGER PRIMARY KEY, created_at TEXT, is_active INTEGER, deleted_at TEXT)');
 DB::connection('growth_fixture')->table('users')->insert([
 ['id'=>1,'created_at'=>'2026-01-01','is_active'=>1,'deleted_at'=>null],
 ['id'=>2,'created_at'=>'2026-02-02','is_active'=>0,'deleted_at'=>null],
 ['id'=>3,'created_at'=>'2026-02-03','is_active'=>1,'deleted_at'=>'2026-03-01'],
 ]);DB::purge('growth_fixture');
}
config(['database.connections.tenant'=>['driver'=>'sqlite','database'=>$files[0],'prefix'=>'']]);
$companies=collect(array_map(fn($file)=>(object)['db_name'=>$file],[$files[0],$files[1],$files[0]]));
$result=app(App\Services\PlatformUserGrowth::class)->data($companies);
if($result['records']!==[['date'=>'2026-01-01','total'=>2,'active'=>2],['date'=>'2026-02-02','total'=>2,'active'=>0]] || $result['unavailable']!==0)throw new RuntimeException('Aggregation, deduplication or deleted-user exclusion failed');
if(config('database.connections.tenant.database')!==$files[0])throw new RuntimeException('Tenant connection changed');
token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/superadmin/companies/metrics.blade.php'))),TOKEN_PARSE);
echo "PASS: tenant aggregation, unique databases, real active counts, deleted-user exclusion, connection isolation and Blade compilation.\n";
}finally{DB::purge('growth_fixture');config(['database.connections.tenant'=>$original]);foreach($files as $file)unlink($file);}
