<?php

require __DIR__.'/payroll_module_management.php';

use App\Models\{Module, User, WorkspaceModule};
use App\Services\{FeatureEntitlements, PlanFeatureEntitlements};
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Database\Schema\Blueprint;

Schema::connection('central')->table('plan_modules', fn (Blueprint $t) => $t->boolean('is_enabled')->default(true));
$payrollId=Module::where('slug','payroll')->value('id');
$payslipsId=Module::where('slug','payslips')->value('id');
$reset=function () { app(PlanFeatureEntitlements::class)->forget(); app(FeatureEntitlements::class)->forget(); };
DB::connection('central')->table('company_modules')->where('company_id',1)->delete();
DB::connection('central')->table('company_modules')->insert(['company_id'=>1,'module_id'=>$payrollId,'is_enabled'=>true]);
DB::connection('central')->table('plan_modules')->where('plan_id',99)->delete();
DB::connection('central')->table('plan_modules')->insert(['plan_id'=>99,'module_id'=>Module::where('slug','tasks')->value('id'),'is_enabled'=>true]);
$reset();
$assert($company->hasFeature('payroll') && $company->hasFeature('payslips'), 'Company-level Payroll enable must grant its inherited Payslips page even when the plan does not provide Payroll.');
foreach (['salary-structures','employee-salary','payroll-processing','payroll-history','formula-builder'] as $child) $assert($company->hasFeature($child), 'Payroll override did not grant inherited tab: '.$child);
$assert(!$secondCompany->hasFeature('payslips') && !$company->hasFeature('employees'), 'Inherited grant leaked into another company or independent feature.');
DB::connection('central')->table('company_modules')->insert(['company_id'=>1,'module_id'=>$payslipsId,'is_enabled'=>false]); $reset();
$assert(!$company->hasFeature('payslips') && $company->hasFeature('payroll-processing'), 'Explicit company child disable was bypassed or changed a sibling.');
DB::connection('central')->table('company_modules')->where('company_id',1)->delete();
DB::connection('central')->table('plan_modules')->where('plan_id',99)->delete();
DB::connection('central')->table('plan_modules')->insert(['plan_id'=>99,'module_id'=>$payslipsId,'is_enabled'=>true]);
$reset();
$assert(app(PlanFeatureEntitlements::class)->allows((object) ['id'=>99,'slug'=>'gold'],'payslips'), 'Explicit plan Payslips grant is ignored by inherited plan mapping.');
$assert($company->hasFeature('payslips'), 'An explicitly enabled plan Payslips feature was ignored when Payroll was granted by the company override.');
$actor=new User(['company_id'=>1,'role'=>'admin']); Auth::guard('web')->setUser($actor);
$assert($actor->canViewModule('payslips'), 'Enabled Payslips feature is hidden from company admin.');
$processing=file_get_contents(resource_path('views/admin/payroll/processing.blade.php'));
preg_match('/@if\(auth\(\)->user\(\)\?->canViewModule\(\'payslips\'\)\)[\s\S]*?View Generated Payslips[\s\S]*?@endif/', $processing, $generated);
$assert(isset($generated[0]), 'Generated Payslips link does not check the destination module.');
$renderGenerated=fn () => Illuminate\Support\Facades\Blade::render($generated[0],['month'=>10,'year'=>2026]);
$assert(str_contains($renderGenerated(),'View Generated Payslips'), 'Enabled Generated Payslips link missing.');
$checkRoute=function (string $name,bool $expected) use ($actor,$assert) {
    $request=Illuminate\Http\Request::create('/payroll/payslips','GET');
    $route=Illuminate\Support\Facades\Route::getRoutes()->getByName($name);
    $request->setRouteResolver(fn () => $route); $request->setUserResolver(fn () => $actor);
    $request->headers->set('Accept','application/json');
    $response=(new App\Http\Middleware\EnforceRolePermissionToggles)->handle($request, fn () => response('allowed'));
    $assert(($response->getStatusCode()===200)===$expected, 'Payslip route and visible feature disagree: '.$name);
};
foreach (['payroll.payslips.index','payroll.payslips.view','payroll.payslips.print','payroll.payslips.pdf','payroll.payslip.view','payroll.payslip.pdf'] as $name) $checkRoute($name,true);
DB::connection('central')->table('plan_modules')->where('plan_id',99)->where('module_id',$payslipsId)->update(['is_enabled'=>false]); $reset();
$assert(!$actor->canViewModule('payslips'), 'Explicit plan disable was bypassed.'); $checkRoute('payroll.payslips.index',false);
$assert(!str_contains($renderGenerated(),'View Generated Payslips'), 'Disabled Generated Payslips link remains visible.');
DB::connection('central')->table('company_modules')->insert(['company_id'=>1,'module_id'=>$payslipsId,'is_enabled'=>true]); $reset();
$assert($actor->canViewModule('payslips'), 'Company-specific enable did not override plan disable.'); $checkRoute('payroll.payslips.index',true);
DB::connection('central')->table('company_modules')->updateOrInsert(['company_id'=>1,'module_id'=>$payrollId],['is_enabled'=>false]); $reset();
$assert(!$actor->canViewModule('payslips'), 'Disabled parent was bypassed.'); $checkRoute('payroll.payslips.index',false);
DB::connection('central')->table('company_modules')->where('company_id',1)->where('module_id',$payrollId)->update(['is_enabled'=>true]); $reset();
WorkspaceModule::where('slug','payslips')->update(['is_active'=>false]);
$assert(!$actor->canViewModule('payslips'), 'Company module disable was bypassed.'); $checkRoute('payroll.payslips.index',false);
WorkspaceModule::where('slug','payslips')->update(['is_active'=>true]);
$assert($actor->canViewModule('payslips'), 'Re-enabled company module remains blocked.'); $checkRoute('payroll.payslips.index',true);
echo "PASS: explicit payroll subfeature grants, company overrides, parent/child/module disables and matching payslip route access.\n";
