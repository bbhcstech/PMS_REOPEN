<?php

// Real renderer integration checks, isolated from tenant databases.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(function (Throwable $exception) { fwrite(STDERR, $exception->getMessage()."\n".$exception->getTraceAsString()."\n"); exit(1); });

use App\Services\{PdfDocument, PdfLetterheadService, DocxGeneratorService};
use setasign\Fpdi\PdfParser\{PdfParser, StreamReader};
use setasign\Fpdi\PdfReader\PdfReader;

foreach (['tenant', 'mysql', 'central'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    Illuminate\Support\Facades\DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array', 'cache.default' => 'array']);

function checkDocument(bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
}
function pdfReader(string $bytes): PdfReader
{
    checkDocument(str_starts_with($bytes, '%PDF'), 'Renderer did not return a PDF.');
    return new PdfReader(new PdfParser(StreamReader::createByString($bytes)));
}
// DejaVu's Identity-H encoded text is stored in PDF literal strings.
function hasPdfText(string $stream, string $text): bool
{
    $encoded = mb_convert_encoding($text, 'UTF-16BE', 'UTF-8');
    $escaped = strtr($encoded, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)', "\r" => '\\r', "\n" => '\\n']);
    return str_contains($stream, $escaped) || str_contains($stream, $text);
}
$artifactDir = storage_path('app/pdf/regression');
Illuminate\Support\Facades\File::ensureDirectoryExists($artifactDir);
function renderReport(string $view, array $data, string $name, string $orientation = 'portrait', bool $multi = false, string $paper = 'a4'): PdfReader
{
    global $artifactDir;
    $pdf = PdfDocument::loadView($view, $data)->setPaper($paper, $orientation);
    $bytes = $pdf->output();
    file_put_contents($artifactDir.'/'.$name.'.pdf', $bytes);
    $reader = pdfReader($bytes);
    if ($multi) checkDocument($reader->getPageCount() > 1, "$name must paginate.");
    for ($page = 1; $page <= $reader->getPageCount(); $page++) {
        checkDocument(hasPdfText($reader->getPage($page)->getContentStream(), 'Page '.$page.' of '.$reader->getPageCount()), "$name page $page lacks its correct page number.");
    }
    echo "PASS: $name ({$reader->getPageCount()} pages)\n";
    return $reader;
}
$user = new App\Models\User(['name' => 'Document Test José', 'email' => 'document@example.test']);
$user->setRelation('employeeDetail', null);
$leaves = [];
$tickets = [];
$attendances = [];
for ($i = 1; $i <= 100; $i++) {
    $leaves[] = (object) ['user' => $user, 'type_label' => 'Annual', 'start_date' => now(), 'end_date' => now(), 'total_days' => 1, 'paid_days' => 1, 'unpaid_days' => 0, 'status' => 'approved', 'is_unpaid' => false];
    $tickets[] = (object) ['ticket_id' => $i, 'company' => (object) ['name' => 'Test Company', 'company_code' => 'TEST'], 'raised_by_name' => $user->name, 'raised_by_email' => $user->email, 'subject' => 'A wrapped support subject to test pagination', 'category' => 'Support', 'priority' => 'high', 'status' => 'open', 'created_at' => now()];
    $attendances[] = (object) ['date' => now()->subDays($i)->format('Y-m-d'), 'status' => 'present', 'clock_in' => '09:00:00', 'clock_out' => '18:00:00'];
}
$leaveReport = renderReport('admin.leaves.exports.pdf', compact('leaves'), 'leave-report', 'landscape', true);
$complaints = renderReport('superadmin.complaints.exports.pdf', ['tickets' => $tickets, 'activeFilters' => ['Status: Open']], 'complaints', 'portrait', true);
$attendance = renderReport('admin.attendance.report_pdf', ['user' => $user, 'attendances' => $attendances, 'month' => 10, 'year' => 2026], 'attendance', 'portrait', true);
checkDocument(hasPdfText($attendance->getPage(1)->getContentStream(), '09:00:00'), 'Attendance duration rendered incorrectly.');
for ($page = 2; $page <= $leaveReport->getPageCount(); $page++) {
    checkDocument(hasPdfText($leaveReport->getPage($page)->getContentStream(), 'Employee'), 'Leave table header was not repeated.');
}
$headings = ['No', 'Name', 'Designation', 'Department', ...range(1, 31), 'Present', 'Absent', 'Leave', 'Holiday', 'Late', 'Half', 'Hours', 'Days'];
$rows = [$headings];
for ($i = 1; $i <= 100; $i++) $rows[] = [$i, $user->name, 'Software Developer', 'Engineering', ...array_fill(0, 31, 'P'), 31, 0, 0, 0, 0, 0, 248, 31];
renderReport('exports.attendance_matrix', ['title' => 'Monthly Attendance — October 2026', 'headings' => $headings, 'rows' => $rows], 'attendance-matrix', 'landscape', true, 'a3');
renderReport('exports.attendance', ['user' => $user, 'month' => 10, 'year' => 2026, 'daysInMonth' => 31, 'attendanceMap' => []], 'attendance-simple');
$requirement = new App\Models\RecruitmentRequirement(['id' => 1, 'title' => 'Developer', 'employment_type' => 'Full time', 'positions' => 2, 'description' => str_repeat("Detailed responsibilities and qualifications for this role.\n", 120), 'salary_range' => '₹50,000–₹75,000', 'status' => 'open', 'created_at' => now()]);
$requirement->setRelation('creator', $user);
$requirement->forceFill(['created_at' => now(), 'id' => 1]);
renderReport('admin.recruitment.pdf', ['requirement' => $requirement, 'policyCard' => ['probation_period' => '3 months', 'hiring_sla' => '30 days', 'pipeline_stages' => ['Review', 'Interview'], 'allowed_file_types' => 'PDF', 'max_resume_size' => '5 MB', 'equal_opportunity' => 'Equal opportunity employer']], 'recruitment', 'portrait', true);
$payroll = (object) ['period_start' => '2026-10-01', 'metadata' => [], 'status' => 'approved', 'pay_date' => '2026-10-31', 'id' => 1];
$payslip = new App\Models\Payslip(['payslip_number' => 'TEST-001', 'employee_snapshot' => ['designation' => 'Developer', 'designation_level' => 7, 'basic' => 50000, 'current_basic' => 50000, 'gross_salary' => 50000, 'total_in_hand' => 48000, 'total_deductions' => 2000, 'working_days' => 22, 'payable_days' => 22], 'updated_at' => now()]);
$payslip->setRelation('user', $user)->setRelation('payroll', $payroll)->setRelation('payrollHistory', null);
$company = (object) ['name' => 'Document Test Company', 'address' => 'Test office', 'email' => 'office@example.test'];
$slip = renderReport('admin.payroll.payslip-pdf', ['payslip' => $payslip, 'company' => $company, 'mode' => 'pdf'], 'payslip');
checkDocument($slip->getPageCount() === 1, 'Standard payslip should fit one A4 page.');
checkDocument(hasPdfText($slip->getPage(1)->getContentStream(), '₹48,000.00'), 'Rupee amount missing from Unicode payslip.');

$letter = ['company_name' => 'Document Test Company', 'subject' => 'Unicode letter — José, ₹50,000', 'recipient_name' => $user->name, 'body' => str_repeat("This is a long professional letter with Unicode: José and ₹50,000. Layout must preserve all content.\n", 85), 'signatory_name' => 'Authorized Person'];
$bytes = PdfLetterheadService::generate($letter)->getContent();
file_put_contents($artifactDir.'/letter.pdf', $bytes);
$letterReader = pdfReader($bytes);
checkDocument($letterReader->getPageCount() > 1, 'Long corporate letter did not paginate.');
checkDocument(hasPdfText($letterReader->getPage(1)->getContentStream(), '₹50,000'), 'Unicode letter text missing.');
echo 'PASS: Unicode image letterhead ('.$letterReader->getPageCount()." pages)\n";

$template = new FPDF;
$template->AddPage();
$template->SetFont('Helvetica', 'B', 18);
$template->Text(20, 15, 'IMPORTED ARTWORK');
$templatePath = $artifactDir.'/artwork.pdf';
$template->Output('F', $templatePath);
$imported = PdfLetterheadService::generate($letter + ['layout_mode' => 'full_a4_page', 'background_page_image' => $templatePath])->getContent();
file_put_contents($artifactDir.'/letter-imported.pdf', $imported);
$importedReader = pdfReader($imported);
checkDocument($importedReader->getPageCount() > 1, 'Imported letterhead did not paginate.');
for ($page = 1; $page <= $importedReader->getPageCount(); $page++) {
    checkDocument(substr_count($importedReader->getPage($page)->getContentStream(), ' Do') === 2, 'Imported artwork missing from a continuation page.');
}
echo "PASS: PDF artwork on all letter pages\n";

$bandBytes = PdfLetterheadService::generate($letter + ['header_image' => $templatePath, 'footer_image' => $templatePath])->getContent();
$bandReader = pdfReader($bandBytes);
for ($page = 1; $page <= $bandReader->getPageCount(); $page++) {
    checkDocument(substr_count($bandReader->getPage($page)->getContentStream(), ' Do') === 3, 'Imported PDF header/footer missing from a continuation page.');
}
file_put_contents($artifactDir.'/letter-pdf-bands.pdf', $bandBytes);
$badArtwork = $artifactDir.'/invalid.pdf';
file_put_contents($badArtwork, 'Invalid PDF');
try {
    PdfLetterheadService::generate($letter + ['header_image' => $badArtwork]);
    throw new RuntimeException('Corrupt PDF artwork accepted.');
} catch (Illuminate\Validation\ValidationException $exception) {
    checkDocument(isset($exception->errors()['header_image']), 'PDF artwork error must identify its upload field.');
}
echo "PASS: PDF header/footer bands repeat and corrupt artwork gives a field error\n";

$png = $artifactDir.'/header.png';
$image = imagecreatetruecolor(1240, 160);
imagefill($image, 0, 0, imagecolorallocate($image, 30, 58, 138));
imagepng($image, $png);
imagedestroy($image);
$footerPng = $artifactDir.'/footer.png';
$image = imagecreatetruecolor(1240, 140);
imagefill($image, 0, 0, imagecolorallocate($image, 71, 85, 105));
imagepng($image, $footerPng);
imagedestroy($image);
$docx = DocxGeneratorService::createDocument($letter + ['header_image' => $png, 'footer_image' => $footerPng], $artifactDir.'/letter.docx');
$zip = new ZipArchive;
checkDocument($zip->open($docx) === true, 'DOCX cannot be opened.');
for ($i = 0; $i < $zip->numFiles; $i++) {
    $name = $zip->getNameIndex($i);
    if (! str_ends_with($name, '.xml') && ! str_ends_with($name, '.rels')) continue;
    $xml = new DOMDocument;
    checkDocument($xml->loadXML($zip->getFromName($name)), 'Invalid OpenXML in '.$name);
}
checkDocument($zip->locateName('word/media/header.png') !== false, 'Word header image missing.');
checkDocument($zip->locateName('word/media/footer.png') !== false, 'Word footer image missing.');
checkDocument(str_contains($zip->getFromName('word/document.xml'), 'headerReference'), 'Repeating Word header reference missing.');
checkDocument(str_contains($zip->getFromName('word/footer1.xml'), 'NUMPAGES'), 'Word page numbering missing.');
checkDocument(str_contains($zip->getFromName('word/document.xml'), '₹50,000'), 'Word Unicode content lost.');
$zip->close();
echo "PASS: DOCX XML, images, repeating header/footer, page fields and Unicode\n";
echo "Artifacts: $artifactDir\n";
