<?php

// Run with: php tests/regression/recruitment_branding_library.php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\RecruitmentController;
use App\Models\{RecruitmentBrandingAsset, RecruitmentRequirement, User};
use App\Support\RecruitmentPdfBranding as Branding;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{Auth, Blade, DB, Schema};
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

function brandingLibraryCheck(bool $ok, string $message): void
{
    if (! $ok) {
        throw new RuntimeException($message);
    }
}

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
    DB::purge($connection);
}
DB::connection('mysql')->setPdo(DB::connection('tenant')->getPdo());
DB::connection('central')->setPdo(DB::connection('tenant')->getPdo());
config(['database.default' => 'tenant', 'session.driver' => 'array']);

foreach ([
    '2026_08_15_000005_create_recruitment_requirements_table.php',
    '2026_10_10_120000_add_pdf_header_footer_to_recruitment_requirements.php',
    '2026_11_26_000002_create_recruitment_branding_assets.php',
] as $file) {
    (require database_path('migrations/tenant/'.$file))->up();
}
Schema::create('users', function ($table) {
    $table->id();
    $table->unsignedBigInteger('company_id');
    $table->string('name');
    $table->string('role');
});
foreach (['departments', 'parent_departments'] as $table) {
    Schema::create($table, function ($schema) {
        $schema->id();
        $schema->string('dpt_name');
    });
}
Schema::create('app_settings', function ($table) {
    $table->id();
    $table->string('key');
    $table->text('value')->nullable();
});
DB::table('users')->insert([
    ['id' => 1, 'company_id' => 4, 'name' => 'Main Admin', 'role' => 'admin'],
    ['id' => 2, 'company_id' => 8, 'name' => 'Other Admin', 'role' => 'admin'],
    ['id' => 3, 'company_id' => 4, 'name' => 'Employee', 'role' => 'employee'],
]);

$controller = new RecruitmentController;
$asUser = function (int $id): User {
    $viewer = User::findOrFail($id);
    $viewer->setRelation('company', null);
    Auth::guard('web')->setUser($viewer);
    session(['current_company_id' => $viewer->company_id]);

    return $viewer;
};
$newRequirement = function (array $fields, array $files = []) use ($controller): RecruitmentRequirement {
    $request = Request::create('/admin/hr/recruitment', 'POST', $fields + [
        'title' => 'Test recruitment requirement', 'positions' => 1, 'employment_type' => 'Full-time',
    ], [], $files);
    $request->setLaravelSession(app('session')->driver());
    app()->instance('request', $request);

    brandingLibraryCheck($controller->store($request)->isRedirect(route('recruitment.index')), 'Create must redirect to Recruitment.');
    return RecruitmentRequirement::latest('id')->firstOrFail();
};
$expectedErrors = function (string $key, array $fields, array $files = []) use ($newRequirement): void {
    try {
        $newRequirement($fields, $files);
        throw new RuntimeException("Expected $key validation failure.");
    } catch (ValidationException $error) {
        brandingLibraryCheck(array_key_exists($key, $error->errors()), "Expected a field error for $key.");
    }
};

$admin = $asUser(1);
$uploads = [
    'pdf_header_image' => UploadedFile::fake()->image('company_header-2026.png', Branding::WIDTH, Branding::HEADER_HEIGHT),
    'pdf_footer_image' => UploadedFile::fake()->image('footer.jpg', Branding::WIDTH, Branding::FOOTER_HEIGHT),
];
$first = $newRequirement(['pdf_footer_name' => 'Main office footer'], $uploads);
$saved = ['pdf_header_asset_id' => $first->pdf_header_asset_id, 'pdf_footer_asset_id' => $first->pdf_footer_asset_id];
$header = RecruitmentBrandingAsset::findOrFail($saved['pdf_header_asset_id']);
$footer = RecruitmentBrandingAsset::findOrFail($saved['pdf_footer_asset_id']);
brandingLibraryCheck($header->name === 'company header 2026' && $footer->name === 'Main office footer', 'Filename default and custom name should both persist.');
brandingLibraryCheck($first->pdfImageDataUri('pdf_header_image') === $header->imageDataUri(), 'PDF must read the stored header.');
brandingLibraryCheck($first->pdfImageDataUri('pdf_footer_image') === $footer->imageDataUri(), 'PDF must read the stored footer.');
$preview = $controller->brandingPreview($footer->id);
brandingLibraryCheck($preview->headers->get('Content-Type') === 'image/jpeg' && $preview->getContent() === $uploads['pdf_footer_image']->get(), 'Saved preview must serve the original bytes.');
brandingLibraryCheck(str_contains($preview->headers->get('Cache-Control'), 'no-store'), 'Saved preview is private and uncached.');

$reuse = $newRequirement($saved);
brandingLibraryCheck($reuse->only(array_keys($saved)) == $saved && RecruitmentBrandingAsset::count() === 2, 'Reusing both images should keep their existing records.');
$mixed = $newRequirement(['pdf_header_asset_id' => $header->id, 'pdf_footer_name' => 'Branch footer'], ['pdf_footer_image' => $uploads['pdf_footer_image']]);
brandingLibraryCheck($mixed->pdf_header_asset_id === $header->id && $mixed->pdf_footer_asset_id !== $footer->id, 'A saved header can be combined with a new named footer.');
$duplicate = $newRequirement(['pdf_footer_name' => $footer->name], $uploads);
brandingLibraryCheck($duplicate->only(array_keys($saved)) == $saved && RecruitmentBrandingAsset::count() === 3, 'Identical uploads under the same name should reuse one record.');

$controller->destroy($first->id);
$newRequirement($saved);
brandingLibraryCheck(RecruitmentBrandingAsset::count() === 3, 'Deleting a requirement must preserve the saved artwork.');
$expectedErrors('pdf_header_asset_id', ['pdf_header_asset_id' => $footer->id, 'pdf_footer_asset_id' => $footer->id]);
$expectedErrors('pdf_header_asset_id', $saved, ['pdf_header_image' => $uploads['pdf_header_image']]);
$expectedErrors('pdf_header_image', ['pdf_footer_asset_id' => $footer->id]);
$expectedErrors('pdf_footer_image', [], ['pdf_header_image' => $uploads['pdf_header_image'], 'pdf_footer_image' => $uploads['pdf_header_image']]);
brandingLibraryCheck(RecruitmentBrandingAsset::count() === 3, 'Invalid requests must leave the library unchanged.');

RecruitmentRequirement::creating(function (RecruitmentRequirement $requirement) {
    if ($requirement->title === 'Simulated failure') {
        throw new RuntimeException('Simulated failure');
    }
});
try {
    $newRequirement(['title' => 'Simulated failure', 'pdf_header_name' => 'Must roll back'], $uploads);
    throw new RuntimeException('Expected requirement creation to fail.');
} catch (RuntimeException $error) {
    brandingLibraryCheck($error->getMessage() === 'Simulated failure' && RecruitmentBrandingAsset::count() === 3, 'A failed requirement must roll back both uploads.');
}

$request = Request::create('/admin/hr/recruitment');
$request->setUserResolver(fn () => $admin);
$assets = $controller->index($request)->getData()['brandingAssets'];
brandingLibraryCheck($assets['header']->pluck('name')->all() === [$header->name] && $assets['footer']->count() === 2, 'Recruitment page exposes separate saved choices.');
brandingLibraryCheck(! array_key_exists('image_data', $assets['header']->first()->getAttributes()), 'The dropdown should load metadata only.');
$fields = <<<'BLADE'
@foreach(\App\Support\RecruitmentPdfBranding::HEIGHTS as $kind => $height)
    @include('admin.recruitment.partials.branding-field', ['assets' => $brandingAssets[$kind]])
@endforeach
BLADE;
$html = Blade::render($fields, ['brandingAssets' => $assets, 'errors' => new ViewErrorBag], true);
brandingLibraryCheck(str_contains($html, '1240 × 160 px') && str_contains($html, '1240 × 140 px')
    && str_contains($html, route('recruitment.branding.preview', $header->id)) && str_contains($html, 'Branch footer'), 'Rendered dropdowns show saved names, preview routes and exact dimensions.');

$legacyFile = tempnam(public_path(), 'rec-legacy-');
try {
    file_put_contents($legacyFile, $uploads['pdf_header_image']->get());
    $legacy = new RecruitmentRequirement(['pdf_header_image' => basename($legacyFile)]);
    brandingLibraryCheck($legacy->pdfImageDataUri('pdf_header_image') === $header->imageDataUri(), 'A historical requirement must still read its file path.');
} finally {
    unlink($legacyFile);
}

$otherAdmin = $asUser(2);
$expectedErrors('pdf_header_asset_id', $saved);
$request->setUserResolver(fn () => $otherAdmin);
brandingLibraryCheck($controller->index($request)->getData()['brandingAssets']->isEmpty(), 'Another company must not receive the saved choices.');
try {
    $controller->brandingPreview($header->id);
    throw new RuntimeException('Another company received the saved preview.');
} catch (ModelNotFoundException $expected) {
}
$asUser(3);
try {
    $controller->brandingPreview($header->id);
    throw new RuntimeException('A non-admin received the admin preview.');
} catch (HttpException $error) {
    brandingLibraryCheck($error->getStatusCode() === 403, 'Only company admins may open branding previews.');
}

echo "Recruitment library: persisted names, preview, reusing either image, deletion, and company isolation passed.\n";
