<?php

// Run with: php -d memory_limit=512M tests/regression/letterhead_signature.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LetterheadSentLetter;
use App\Models\LetterheadSignature;
use App\Models\User;
use App\Services\DocxGeneratorService;
use App\Services\LetterheadSignatureService;
use App\Services\PdfLetterheadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

$failures = 0;
$check = function (bool $ok, string $message) use (&$failures) {
    echo ($ok ? '  PASS ' : '  FAIL ') . $message . "\n";
    if (! $ok) {
        $failures++;
    }
};

// Isolate database and private storage for offline regression testing
Config::set('database.default', 'tenant');
Config::set('database.connections.tenant', [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);
DB::purge('tenant');
DB::setDefaultConnection('tenant');
Storage::fake('local');

(require database_path('migrations/tenant/2026_11_28_000001_create_letterhead_signatures_table.php'))->up();
(require database_path('migrations/tenant/2026_10_10_160000_create_letterhead_sent_letters_table.php'))->up();
(require database_path('migrations/tenant/2026_11_28_000002_add_signature_snapshot_to_letterhead_sent_letters_table.php'))->up();

// 1. Routes exist
foreach ([
    'letterhead.signatures.index',
    'letterhead.signatures.store',
    'letterhead.signatures.update',
    'letterhead.signatures.default',
    'letterhead.signatures.toggle',
    'letterhead.signatures.destroy',
    'letterhead.signatures.image',
] as $routeName) {
    $check(Route::has($routeName), "route {$routeName} exists");
}

// Helper to create PNG data URLs
$makePngDataUrl = function (bool $blank): string {
    $img = imagecreatetruecolor(240, 80);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    if (! $blank) {
        $ink = imagecolorallocatealpha($img, 15, 23, 42, 0);
        imageline($img, 20, 50, 210, 30, $ink);
        imageline($img, 30, 35, 190, 55, $ink);
    }
    ob_start();
    imagepng($img);
    $bytes = ob_get_clean();
    imagedestroy($img);
    return 'data:image/png;base64,' . base64_encode($bytes);
};

// 2. Reject blank drawn signature
$blankRejected = false;
try {
    LetterheadSignatureService::storeFromDataUrl($makePngDataUrl(true), 1);
} catch (ValidationException $e) {
    $blankRejected = true;
}
$check($blankRejected, 'blank canvas signature is rejected');

// 3. Store valid drawn signature privately
$drawnStored = LetterheadSignatureService::storeFromDataUrl($makePngDataUrl(false), 1);
$check(
    Storage::disk('local')->exists($drawnStored['path'])
    && ! str_starts_with($drawnStored['path'], 'public/')
    && $drawnStored['width_px'] === 240
    && $drawnStored['height_px'] === 80,
    'drawn PNG signature stored in private local disk with exact dimensions'
);

// 4. Upload validation: valid PNG accepted, spoofed/invalid & oversized rejected
$tmpValid = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sig_valid_' . bin2hex(random_bytes(4)) . '.png';
file_put_contents($tmpValid, base64_decode(substr($makePngDataUrl(false), strlen('data:image/png;base64,'))));
$validUpload = new UploadedFile($tmpValid, 'sig.png', 'image/png', null, true);
$uploadedStored = LetterheadSignatureService::storeFromUploadedFile($validUpload, 1);
$check(Storage::disk('local')->exists($uploadedStored['path']), 'valid uploaded PNG signature stored privately');
@unlink($tmpValid);

$tmpFake = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sig_fake_' . bin2hex(random_bytes(4)) . '.png';
file_put_contents($tmpFake, 'not-a-real-png-payload');
$fakeUpload = new UploadedFile($tmpFake, 'fake.png', 'image/png', null, true);
$fakeRejected = false;
try {
    LetterheadSignatureService::storeFromUploadedFile($fakeUpload, 1);
} catch (ValidationException $e) {
    $fakeRejected = true;
}
$check($fakeRejected, 'corrupt/spoofed signature image content is rejected');
@unlink($tmpFake);

$tmpBig = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sig_big_' . bin2hex(random_bytes(4)) . '.png';
file_put_contents($tmpBig, str_repeat('A', (LetterheadSignatureService::maxUploadKb() + 100) * 1024));
$bigUpload = new UploadedFile($tmpBig, 'big.png', 'image/png', null, true);
$bigRejected = false;
try {
    LetterheadSignatureService::storeFromUploadedFile($bigUpload, 1);
} catch (ValidationException $e) {
    $bigRejected = true;
}
$check($bigRejected, 'oversized signature upload (>2 MB) is rejected');
@unlink($tmpBig);

// 5. Authorization & tenant isolation
$sig = LetterheadSignature::create([
    'company_id' => 10,
    'user_id' => 100,
    'signatory_name' => 'Org 10 Admin',
    'signatory_title' => 'HR Director',
    'image_path' => $drawnStored['path'],
    'mime_type' => 'image/png',
    'width_px' => 240,
    'height_px' => 80,
    'method' => 'draw',
    'is_default' => true,
    'is_active' => true,
]);

$owner = new User(['role' => 'employee', 'company_id' => 10]);
$owner->id = 100;
$otherEmployee = new User(['role' => 'employee', 'company_id' => 10]);
$otherEmployee->id = 101;
$otherOrgAdmin = new User(['role' => 'admin', 'company_id' => 20]);
$otherOrgAdmin->id = 200;

$ownerAllowed = true;
try {
    auth()->setUser($owner);
    LetterheadSignatureService::authorizeSignature($owner, $sig, true);
} catch (\Throwable $e) {
    $ownerAllowed = false;
}
$check($ownerAllowed, 'owning user is authorized for their signature');

$otherEmployeeBlocked = false;
try {
    auth()->setUser($otherEmployee);
    LetterheadSignatureService::authorizeSignature($otherEmployee, $sig, true);
} catch (\Throwable $e) {
    $otherEmployeeBlocked = true;
}
$check($otherEmployeeBlocked, 'non-admin user cannot access another user signature');

$otherOrgBlocked = false;
try {
    auth()->setUser($otherOrgAdmin);
    LetterheadSignatureService::authorizeSignature($otherOrgAdmin, $sig, true);
} catch (\Throwable $e) {
    $otherOrgBlocked = true;
}
$check($otherOrgBlocked, 'cross-organization signature access is blocked');

// 6. Finalized snapshot integrity when saved signature is deleted
$absSig = LetterheadSignatureService::absolutePath($sig->image_path);
$snapshotPath = LetterheadSignatureService::createSnapshotFromAbsolute($absSig, 10);
$sentLetter = LetterheadSentLetter::create([
    'company_id' => 10,
    'subject' => 'Signed Offer',
    'body' => 'Welcome aboard.',
    'signatory_name' => $sig->signatory_name,
    'signatory_title' => $sig->signatory_title,
    'signature_id' => $sig->id,
    'signature_snapshot_path' => $snapshotPath,
    'signature_alignment' => 'left',
    'signature_width_mm' => 40,
    'signature_applied_by' => 100,
    'signature_applied_at' => now(),
]);
$sig->delete();
LetterheadSignatureService::deletePrivateFile($drawnStored['path']);
$check(
    $sentLetter->signature_snapshot_path && Storage::disk('local')->exists($sentLetter->signature_snapshot_path),
    'finalized document retains signature snapshot after saved signature is deleted'
);

// 7. PDF generation with & without signature
$snapAbs = LetterheadSignatureService::absolutePath($snapshotPath);
$signedPdf = PdfLetterheadService::generate([
    'subject' => 'Signed PDF Test',
    'body' => str_repeat("Line of official communication.\n\n", 18),
    'signatory_name' => 'Org 10 Admin',
    'signatory_title' => 'HR Director',
    'signature_image' => $snapAbs,
    'signature_alignment' => 'center',
    'signature_width_mm' => 42,
])->getContent();
$check(str_starts_with($signedPdf, '%PDF-'), 'signed multi-page PDF generated cleanly');

$unsignedPdf = PdfLetterheadService::generate([
    'subject' => 'Unsigned PDF Test',
    'body' => 'Short unsigned letter.',
    'signatory_name' => 'Org 10 Admin',
    'signatory_title' => 'HR Director',
])->getContent();
$check(str_starts_with($unsignedPdf, '%PDF-'), 'unsigned PDF fallback continues working');

// 8. Word (.docx) generation with & without signature
$signedDocx = DocxGeneratorService::createDocument([
    'subject' => 'Signed Word Test',
    'body' => 'Body content for signed Word export.',
    'signatory_name' => 'Org 10 Admin',
    'signatory_title' => 'HR Director',
    'signature_image' => $snapAbs,
    'signature_alignment' => 'right',
    'signature_width_mm' => 40,
]);
$zip = new ZipArchive();
$zip->open($signedDocx);
$hasSigMedia = $zip->locateName('word/media/signature.png') !== false;
$relsXml = $zip->getFromName('word/_rels/document.xml.rels') ?: '';
$docXml = $zip->getFromName('word/document.xml') ?: '';
$zip->close();
@unlink($signedDocx);
$check(
    $hasSigMedia && str_contains($relsXml, 'rIdSignature') && str_contains($docXml, 'r:embed="rIdSignature"'),
    'signed Word (.docx) embeds word/media/signature.png and rIdSignature'
);

$unsignedDocx = DocxGeneratorService::createDocument([
    'subject' => 'Unsigned Word Test',
    'body' => 'Body content for unsigned Word export.',
]);
$zip2 = new ZipArchive();
$zip2->open($unsignedDocx);
$noSigMedia = $zip2->locateName('word/media/signature.png') === false;
$zip2->close();
@unlink($unsignedDocx);
$check($noSigMedia, 'unsigned Word (.docx) omits signature media');

echo $failures ? "\nFAIL: {$failures} check(s) failed\n" : "\nPASS: all letterhead signature checks passed.\n";
exit($failures ? 1 : 0);
