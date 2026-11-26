<?php

// Run with: php tests/regression/recruitment_broadcast_save.php  (isolated SQLite; no live data)
// "Create & Broadcast Requirement" must not return a 500:
//  A. company databases that have not run the branding migration are repaired on the fly;
//  B. branding images larger than the MySQL packet limit are re-encoded to fit (same dimensions),
//     or rejected with a clear message.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\RecruitmentController;
use App\Models\{RecruitmentBrandingAsset, RecruitmentRequirement, User};
use App\Support\RecruitmentPdfBranding;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{Auth, DB, Schema};

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant', 'session.driver' => 'array']);
Schema::create('users', function ($t) { $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->string('name'); $t->string('email')->nullable(); $t->string('password')->nullable(); $t->string('role')->nullable(); $t->boolean('is_active')->default(true); $t->timestamps(); });
Schema::create('departments', function ($t) { $t->id(); $t->string('dpt_name')->nullable(); $t->timestamps(); });
Schema::create('notifications', function ($t) { $t->uuid('id')->primary(); $t->string('type'); $t->string('notifiable_type'); $t->unsignedBigInteger('notifiable_id'); $t->text('data'); $t->timestamp('read_at')->nullable(); $t->timestamps(); });
// An older company database: recruitment table WITHOUT the branding table or columns.
Schema::create('recruitment_requirements', function ($t) {
    $t->id(); $t->unsignedBigInteger('company_id')->nullable(); $t->string('title'); $t->unsignedBigInteger('department_id')->nullable(); $t->string('department_name')->nullable();
    $t->integer('positions')->default(1); $t->string('employment_type')->nullable(); $t->string('experience_required')->nullable(); $t->string('salary_range')->nullable();
    $t->string('location')->nullable(); $t->text('description')->nullable(); $t->text('requirements_summary')->nullable(); $t->string('status')->nullable();
    $t->unsignedBigInteger('created_by')->nullable(); $t->timestamps();
});
DB::table('users')->insert([['id' => 1, 'company_id' => 1, 'name' => 'Admin', 'role' => 'admin'], ['id' => 2, 'company_id' => 1, 'name' => 'Employee', 'role' => 'employee']]);
Auth::guard('web')->setUser(User::findOrFail(1));
session(['current_company_id' => 1]);

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};
$noisyPng = function (int $w, int $h): string {
    $img = imagecreatetruecolor($w, $h);
    mt_srand(7);
    for ($y = 0; $y < $h; $y++) for ($x = 0; $x < $w; $x++) imagesetpixel($img, $x, $y, mt_rand(0, 0xFFFFFF));
    ob_start(); imagepng($img, null, 9); $png = ob_get_clean(); imagedestroy($img);
    return $png;
};
$upload = function (string $bytes, string $name) {
    $path = tempnam(sys_get_temp_dir(), 'rb') . '.png';
    file_put_contents($path, $bytes);
    return new UploadedFile($path, $name, 'image/png', null, true);
};
$broadcast = function (array $data, array $files) {
    $request = Request::create('/admin/hr/recruitment', 'POST', ['title' => 'QA Engineer', 'positions' => 2, 'employment_type' => 'Full-time', 'salary_min' => '4', 'salary_max' => '6'] + $data, [], $files);
    $request->setLaravelSession(app('session')->driver());
    app()->instance('request', $request);
    try {
        return (new RecruitmentController)->store($request);
    } catch (Illuminate\Validation\ValidationException $e) {
        return $e;
    }
};
$header = $noisyPng(RecruitmentPdfBranding::WIDTH, RecruitmentPdfBranding::HEADER_HEIGHT);
$footer = $noisyPng(RecruitmentPdfBranding::WIDTH, RecruitmentPdfBranding::FOOTER_HEIGHT);

// A. Missing branding schema is created on the fly; the requirement is saved and broadcast.
$response = $broadcast([], ['pdf_header_image' => $upload($header, 'header.png'), 'pdf_footer_image' => $upload($footer, 'footer.png')]);
$check($response instanceof Illuminate\Http\RedirectResponse && $response->getTargetUrl() === route('recruitment.index'), 'Create & Broadcast succeeds on a company database without the branding migration');
$check(Schema::hasTable('recruitment_branding_assets') && Schema::hasColumn('recruitment_requirements', 'pdf_header_asset_id'), 'branding table and columns are created automatically');
$requirement = RecruitmentRequirement::latest('id')->first();
$check($requirement && $requirement->salary_range === '4 - 6 LPA' && $requirement->pdf_header_asset_id && $requirement->pdf_footer_asset_id, 'requirement is saved with CTC and both branding images');
$check(DB::table('notifications')->count() === 2, 'the requirement is broadcast to company staff');

// B. Packet limit: large images are re-encoded to fit, keeping exact dimensions.
$fitted = RecruitmentPdfBranding::fitForDatabase($header, 'image/png', 256 * 1024);
$size = $fitted ? getimagesizefromstring($fitted['bytes']) : null;
$check(strlen($header) > 200 * 1024 && $fitted && $fitted['mime'] === 'image/jpeg' && 4 * ceil(strlen($fitted['bytes']) / 3) <= 256 * 1024 - 64 * 1024, 'an image too large for a small MySQL packet is re-encoded to fit');
$check($size && $size[0] === RecruitmentPdfBranding::WIDTH && $size[1] === RecruitmentPdfBranding::HEADER_HEIGHT, 're-encoded image keeps the exact required dimensions');
$small = RecruitmentPdfBranding::fitForDatabase('tiny', 'image/png', 1024 * 1024);
$check($small === ['bytes' => 'tiny', 'mime' => 'image/png'], 'images that already fit are stored unchanged');
$tight = RecruitmentPdfBranding::fitForDatabase(random_bytes(400 * 1024), 'image/png', 128 * 1024);
$check($tight === null, 'data that cannot be made to fit returns null (shown to the user as a clear message, not a 500)');

// C. Reusing saved images (no upload) also works after the repair.
$assets = RecruitmentBrandingAsset::orderBy('id')->get()->keyBy('kind');
$response = $broadcast(['title' => 'Second role', 'pdf_header_asset_id' => $assets['header']->id, 'pdf_footer_asset_id' => $assets['footer']->id], []);
$check($response instanceof Illuminate\Http\RedirectResponse && RecruitmentRequirement::count() === 2, 'a requirement using saved header/footer images is created');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: Create & Broadcast Requirement saves without server errors.\n";
exit($failures ? 1 : 0);
