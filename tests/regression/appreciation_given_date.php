<?php

require __DIR__.'/award_recognition_schema.php';

use App\Http\Controllers\AwardController;
use App\Models\{Appreciations, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade, DB, Schema};

config(['database.default'=>'tenant', 'database.connections.central'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>''], 'session.driver'=>'array']);
DB::purge('central');
Schema::create('appreciations', function ($t) {
    $t->id(); $t->string('title'); $t->string('color_code'); $t->string('status')->default('active');
    $t->string('summary')->nullable(); $t->string('icon')->nullable(); $t->timestamps();
});
DB::table('appreciations')->insert(['id'=>1,'title'=>'Existing template','color_code'=>'#FF0000','status'=>'active']);
$migration=require base_path('database/migrations/tenant/2026_10_15_130000_add_given_fields_to_appreciations.php');
$migration->up(); $migration->up();
$check=fn ($condition,$message) => $condition ?: throw new RuntimeException($message);
$check(Appreciations::findOrFail(1)->given_on === null, 'Migration fabricated dates for existing templates.');
$actor=new class extends User {
    public function hasModulePermission(string $moduleSlug, string $permission = 'view'): bool { return true; }
};
$actor->forceFill(['id'=>1,'company_id'=>1,'role'=>'admin']);
Auth::guard('web')->setUser($actor);
$controller=new AwardController;
$payload=['title'=>'Employee of the month','color_code'=>'#FF0000','status'=>'active','given_to'=>'Sales Team','given_on'=>'2026-10-15','summary'=>'Excellent work','icon'=>'fa-trophy'];
$controller->appreciationStore(Request::create('/awards/appreciation-store','POST',$payload));
$appreciation=Appreciations::latest('id')->firstOrFail();
$check($appreciation->given_on->format('Y-m-d') === '2026-10-15' && $appreciation->given_to === 'Sales Team', 'Create discarded date or recipient.');
$check($appreciation->summary === 'Excellent work' && $appreciation->icon === 'fa-trophy', 'Create discarded other form metadata.');
$controller->appreciationUpdate(Request::create('/awards/appreciation-update','PUT',array_replace($payload,['given_on'=>'2026-10-20'])),$appreciation->id);
$appreciation->refresh();
$check($appreciation->given_on->format('Y-m-d') === '2026-10-20', 'Edited date did not persist.');
foreach (['2026-02-30','invalid','10/20/2026'] as $invalid) {
    try { $controller->appreciationUpdate(Request::create('/update','PUT',array_replace($payload,['given_on'=>$invalid])),$appreciation->id); throw new RuntimeException('Invalid date accepted.'); }
    catch (Illuminate\Validation\ValidationException $e) {}
}
$check($appreciation->fresh()->given_on->format('Y-m-d') === '2026-10-20', 'Invalid date altered the saved record.');
$view=file_get_contents(resource_path('views/admin/awards/appreciation-index.blade.php'));
preg_match('/<td>\{\{ \$appreciation->given_on .*?<\/td>/', $view, $matches);
$check(str_contains(Blade::render($matches[0],['appreciation'=>$appreciation]),'20 Oct 2026'), 'Saved Given On date is missing from the table.');
$partial=Blade::render('@include("admin.awards.partials.appreciation-given-fields")',['appreciation'=>$appreciation,'errors'=>new Illuminate\Support\ViewErrorBag]);
$check(str_contains($partial,'value="2026-10-20"'), 'Edit input did not restore saved date in native date format.');
$controller->appreciationStore(Request::create('/store','POST',['title'=>'Optional date','color_code'=>'#FF0000']));
$check(Appreciations::latest('id')->first()->given_on === null, 'Omitted optional date was fabricated.');
$edit=file_get_contents(resource_path('views/admin/awards/appreciation-edit.blade.php'));
$check(!str_contains($edit,'$award->'), 'Appreciation edit uses wrong record variable.');
foreach (['appreciation-create','appreciation-edit','partials/appreciation-given-fields'] as $name) token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/awards/'.$name.'.blade.php'))),TOKEN_PARSE);
echo "PASS: appreciation date creation, editing, strict validation, table display, native date input, optional dates and idempotent schema upgrade.\n";
