<?php
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB, Schema, Auth};
use App\Models\User;
use App\Http\Controllers\Admin\AppraisalController;
config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'database.default' => 'tenant']);
DB::purge('tenant');
Schema::create('users', function ($t) {
    $t->id(); $t->integer('company_id'); $t->string('name'); $t->string('email'); $t->string('role'); $t->boolean('is_active')->default(true); $t->string('designation')->nullable();
});
Schema::create('appraisals', function ($t) {
    $t->id(); $t->integer('company_id'); $t->integer('employee_id'); $t->integer('evaluated_by')->nullable(); $t->string('appraisal_period'); $t->decimal('overall_score'); $t->softDeletes();
});
foreach ([[1, 1, 'employee'], [2, 1, 'employee'], [3, 1, 'manager'], [4, 2, 'employee']] as [$id, $company, $role]) {
    DB::table('users')->insert(['id' => $id, 'company_id' => $company, 'name' => 'Person '.$id, 'email' => 'person'.$id.'@example.test', 'role' => $role]);
    DB::table('appraisals')->insert(['company_id' => $company, 'employee_id' => $id, 'appraisal_period' => '2026 Q3', 'overall_score' => 70 + $id]);
}
Auth::guard('web')->setUser(User::findOrFail(1));
$controller = new AppraisalController;
foreach ([[], ['employee_id' => 2], ['search' => 'Person 3']] as $params) {
    $data = $controller->index(\Illuminate\Http\Request::create('/admin/hr/appraisal', 'GET', $params))->getData();
    foreach ($data['appraisals'] as $record) {
        if ($record->employee_id !== 1) throw new RuntimeException('Another person appraisal leaked.');
    }
    if ($params === [] && ($data['totalEvaluated'] !== 1 || $data['avgScore'] != 71)) throw new RuntimeException('Summary includes other employees.');
}
foreach ([fn () => $controller->store(new \Illuminate\Http\Request), fn () => $controller->autoCalculate(new \Illuminate\Http\Request), fn () => $controller->destroy(2)] as $mutation) {
    try { $mutation(); throw new RuntimeException('Employee could modify appraisals.'); }
    catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { if ($e->getStatusCode() !== 403) throw $e; }
}
if (DB::table('appraisals')->count() !== 4) throw new RuntimeException('Employee view changed appraisal records.');
echo "PASS: employee appraisal tables and summaries are self-only; forged filters and mutations cannot access other records.\n";
