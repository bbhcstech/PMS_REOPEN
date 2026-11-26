<?php

// Run with: php tests/regression/recruitment_pdf_sheet.php  (synthetic data; no database rows are written)
// Recruitment Specification Sheet built with pdfkit + pdf-lib: one A4 page even for long text,
// company header at the top and footer at the bottom, experience with its unit, CTC in LPA, metadata.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{RecruitmentRequirement, User};
use App\Services\RecruitmentPdfRenderer;
use App\Support\RecruitmentFormat as F;
use Symfony\Component\Process\Process;

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) $failures++;
};

// 1. Display formats
$check(F::experience('4') === '4 Years' && F::experience('1') === '1 Year' && F::experience('2-4') === '2 - 4 Years' && F::experience('5+') === '5+ Years', 'experience gets its unit (4 Years, 1 Year, 2 - 4 Years, 5+ Years)');
$check(F::experience('No') === 'Fresher (0 Years)' && F::experience('') === 'Not Specified' && F::experience('2-3yrs') === '2-3 Years' && F::experience('6 months') === '6 months', 'fresher, empty and texts that already carry a unit');
$check(F::ctc('4.5 - 6') === '4.5 - 6 LPA' && F::ctc('8') === '8 LPA' && F::ctc('4.5 - 6 LPA') === '4.5 - 6 LPA', 'CTC figures are shown in LPA');
$check(F::ctc('10000') === '10000' && F::ctc('₹ 50000') === '₹ 50000' && F::ctc('') === 'Negotiable', 'older full amounts stay exactly as saved; empty is Negotiable');

// 2. Rendered PDF
if (! is_dir(base_path('node_modules/pdfkit')) || ! is_dir(base_path('node_modules/pdf-lib'))) {
    echo "  SKIP PDF rendering: run npm install (pdfkit, pdf-lib)\n";
} else {
    $png = function (int $w, int $h): string { $img = imagecreatetruecolor($w, $h); ob_start(); imagepng($img); return 'data:image/png;base64,' . base64_encode(ob_get_clean()); };
    $requirement = new class extends RecruitmentRequirement {
        public ?string $h = null; public ?string $f = null;
        public function pdfImageDataUri(string $column): ?string { return $column === 'pdf_header_image' ? $this->h : $this->f; }
    };
    $requirement->forceFill(['id' => 9, 'title' => 'Senior Engineer', 'department_name' => 'Engineering', 'employment_type' => 'Full-time', 'positions' => 2,
        'experience_required' => '4', 'salary_range' => '4.5 - 6 LPA', 'location' => 'Kolkata', 'status' => 'open',
        'description' => str_repeat('Design, build and ship multi-tenant features. ', 120), 'requirements_summary' => str_repeat('Laravel, React and MySQL. ', 90)]);
    $requirement->created_at = now();
    $requirement->setRelation('creator', new User(['name' => 'HR Lead']));
    $requirement->h = $png(1800, 240);
    $requirement->f = $png(1800, 160);
    $policy = ['code' => 'POL-REC-2026', 'probation_period' => '3 Months', 'hiring_sla' => '30 Days', 'pipeline_stages' => ['Applied', 'Offer'], 'allowed_file_types' => 'PDF,DOC', 'max_resume_size' => '5 MB', 'equal_opportunity' => 'EEO'];

    $bytes = app(RecruitmentPdfRenderer::class)->render($requirement, $policy);
    $check(str_starts_with($bytes, '%PDF'), 'pdfkit + pdf-lib produce a PDF');
    $file = tempnam(sys_get_temp_dir(), 'req') . '.pdf';
    file_put_contents($file, $bytes);
    // Inspect the result with pdf-lib itself: pages, metadata and where the images are drawn.
    $inspect = new Process([config('services.node.binary', 'node'), '--input-type=module', '-e', <<<'JS'
        import { PDFDocument } from 'pdf-lib';
        import { readFileSync } from 'node:fs';
        const pdf = await PDFDocument.load(readFileSync(process.argv[1]));
        const page = pdf.getPage(0);
        console.log(JSON.stringify({ pages: pdf.getPageCount(), width: Math.round(page.getWidth()), height: Math.round(page.getHeight()), title: pdf.getTitle(), producer: pdf.getProducer(), author: pdf.getAuthor() }));
        JS, $file], base_path());
    $inspect->run();
    @unlink($file);
    $info = json_decode(trim($inspect->getOutput()), true) ?: [];
    $check(($info['pages'] ?? 0) === 1, 'long description and qualifications still fit on a single page');
    $check(($info['width'] ?? 0) === 595 && ($info['height'] ?? 0) === 842, 'page is A4 portrait');
    $check(($info['title'] ?? '') === 'Job Requirement — Senior Engineer' && str_contains($info['producer'] ?? '', 'pdf-lib'), 'document title and producer metadata are set by pdf-lib');

    $payload = app(RecruitmentPdfRenderer::class)->payload($requirement, $policy);
    $check($payload['job']['experience'] === '4 Years' && $payload['job']['salary'] === '4.5 - 6 LPA' && $payload['job']['positions'] === '2 Positions', 'sheet shows "4 Years", "4.5 - 6 LPA" and "2 Positions"');
    $check($payload['images']['header']['mime'] === 'image/png' && $payload['images']['footer']['mime'] === 'image/png', 'company header and footer artwork are passed to the generator');
    $check($payload['policy']['resume'] === 'PDF, DOC (max 5 MB)', 'resume formats are listed cleanly');
}

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: recruitment sheet formats and single-page pdfkit + pdf-lib output.\n";
exit($failures ? 1 : 0);
