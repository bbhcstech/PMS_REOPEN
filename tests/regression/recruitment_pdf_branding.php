<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\RecruitmentPdfBranding as Branding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

function brandingCheck($ok, $message) { if (! $ok) throw new RuntimeException($message); }
$paths = [];
$image = function ($width, $height, $extension = 'png') use (&$paths) {
    $path = tempnam(sys_get_temp_dir(), 'rec-brand-'); $paths[] = $path;
    $resource = imagecreatetruecolor($width, $height);
    imagefill($resource, 0, 0, imagecolorallocate($resource, 30, 64, 175));
    $extension === 'jpg' ? imagejpeg($resource, $path) : imagepng($resource, $path);
    imagedestroy($resource);
    return new UploadedFile($path, 'banner.'.$extension, null, null, true);
};
$validate = fn ($file, $height) => Validator::make(['pdf_header_image' => $file],
    ['pdf_header_image' => Branding::rules($height)], Branding::messages());
try {
    $header = $image(1800, 240);
    $footer = $image(1800, 160, 'jpg');
    brandingCheck($validate($header, 240)->passes(), 'Valid PNG header rejected.');
    brandingCheck($validate($footer, 160)->passes(), 'Valid JPEG footer rejected.');
    brandingCheck($validate($header, 160)->fails(), 'Header dimensions accepted for footer.');
    brandingCheck($validate($image(1799, 240), 240)->fails(), 'Wrong width accepted.');
    brandingCheck($validate(null, 240)->fails(), 'Missing banner accepted.');
    brandingCheck($validate(new UploadedFile($header->getPathname(), 'banner.pdf', null, null, true), 240)->fails(), 'Invalid extension accepted.');
    $fake = tempnam(sys_get_temp_dir(), 'rec-fake-'); $paths[] = $fake;
    file_put_contents($fake, '%PDF-1.4 not an image');
    brandingCheck($validate(new UploadedFile($fake, 'banner.png', null, null, true), 240)->fails(), 'Spoofed PNG accepted.');
    $large = $image(1800, 240);
    file_put_contents($large->getPathname(), str_repeat('x', 2097153), FILE_APPEND);
    clearstatcache(true, $large->getPathname());
    brandingCheck($validate($large, 240)->fails(), 'Oversized image accepted.');

    $requirement = new class extends App\Models\RecruitmentRequirement {
        public array $artwork = [];
        public function pdfImageDataUri(string $column): ?string { return $this->artwork[$column] ?? null; }
    };
    $requirement->forceFill(['id' => 1, 'title' => 'Recruitment branding regression', 'employment_type' => 'Full-time',
        'positions' => 2, 'status' => 'open', 'created_at' => now(), 'pdf_header_image' => 'header.png', 'pdf_footer_image' => 'footer.jpg',
        'description' => str_repeat("Responsibilities and project delivery requirements.\n", 140)]);
    $requirement->setRelation('creator', null);
    $requirement->artwork = ['pdf_header_image' => 'data:image/png;base64,'.base64_encode(file_get_contents($header->getPathname())),
        'pdf_footer_image' => 'data:image/jpeg;base64,'.base64_encode(file_get_contents($footer->getPathname()))];
    $policyCard = ['probation_period' => '3 months', 'hiring_sla' => '30 days', 'pipeline_stages' => ['Applied', 'Interview'],
        'allowed_file_types' => 'PDF', 'max_resume_size' => '5 MB', 'equal_opportunity' => 'Yes'];
    $pdf = App\Services\PdfDocument::loadView('admin.recruitment.pdf', compact('requirement', 'policyCard'))->setPaper('a4', 'portrait');
    $frames = [];
    $dom = $pdf->getDomPDF();
    $dom->setCallbacks(array_merge($dom->getCallbacks(), [[
        'event' => 'end_frame', 'f' => function ($frame) use (&$frames) {
            if ($frame->get_node()->nodeName === 'img') $frames[] = $frame->get_border_box();
        },
    ]]));
    $output = $pdf->output();
    $pages = $dom->getCanvas()->get_page_count();
    brandingCheck(str_starts_with($output, '%PDF-') && $pages >= 2, 'Multi-page PDF failed.');
    brandingCheck(count($frames) === $pages * 2, 'Branding did not repeat on every page.');
    foreach ($frames as $box) {
        brandingCheck($box['x'] >= 40 && $box['x'] + $box['w'] <= 555, 'Banner exceeds A4 content width.');
        brandingCheck($box['y'] >= 0 && $box['y'] + $box['h'] <= 842, 'Banner outside page: '.json_encode($box));
        brandingCheck($box['y'] + $box['h'] < 119 || $box['y'] > 745, 'Banner overlaps reserved content area.');
    }
    echo "Recruitment branding: types, extensions, exact dimensions, file size and repeated multi-page PDF banners passed.\n";
} finally {
    foreach ($paths as $path) if (is_file($path)) unlink($path);
}
