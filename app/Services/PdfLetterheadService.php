<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

class PdfLetterheadService
{
    /**
     * Generate PDF with Letterhead (handling PDF backgrounds via FPDI and image backgrounds via DomPDF/FPDI).
     */
    public static function generate(array $letterData, ?object $letterhead = null, string $fileName = 'Letter.pdf'): Response
    {
        // Require standalone FPDI autoloader if not yet autoloaded
        if (! class_exists(Fpdi::class)) {
            $fpdfPath = base_path('vendor/setasign/fpdf/fpdf.php');
            $fpdiPath = base_path('vendor/setasign/fpdi/src/autoload.php');
            if (file_exists($fpdfPath)) {
                require_once $fpdfPath;
            }
            if (file_exists($fpdiPath)) {
                require_once $fpdiPath;
            }
        }

        $layoutMode = $letterData['layout_mode'] ?? ($letterhead?->layout_mode ?: 'custom_header_footer');
        $bgFile = $letterData['background_page_image'] ?? ($letterhead?->background_page_image ?? null);
        $headerFile = $letterData['header_image'] ?? ($letterhead?->header_image ?? null);
        $footerFile = $letterData['footer_image'] ?? ($letterhead?->footer_image ?? null);

        // Fallbacks
        if ($layoutMode === 'full_a4_page' && ! $bgFile) {
            $bgFile = 'assets/letterhead/presets/bengal_it_hub_a4.svg';
        }

        $fullBgPath = $bgFile ? public_path($bgFile) : null;
        $isBgPdf = $fullBgPath && file_exists($fullBgPath) && strtolower(pathinfo($fullBgPath, PATHINFO_EXTENSION)) === 'pdf';

        // SCENARIO 1: Whole A4 Page Background is a PDF File -> Use FPDI to overlay content
        if ($isBgPdf && class_exists(Fpdi::class)) {
            return self::generateWithFpdi($fullBgPath, $letterData, $fileName);
        }

        // SCENARIO 2: Image background / Header-Footer images / Standard HTML -> Use DomPDF
        $paperSize = $letterhead?->paper_size ?: 'a4';
        $orientation = $letterhead?->orientation ?: 'portrait';

        $letter = array_merge([
            'ref_no' => 'REF/' . date('Y') . '/' . strtoupper(Str::random(6)),
            'date' => now()->format('F d, Y'),
            'recipient_name' => '',
            'recipient_email' => '',
            'subject' => 'Official Letter',
            'body' => '',
            'body_paragraphs' => [],
            'signatory_name' => auth()->user()?->name ?: 'Arthur Pendelton',
            'signatory_title' => 'Authorized Signatory',
            'layout_mode' => $layoutMode,
            'header_image' => $headerFile,
            'footer_image' => $footerFile,
            'background_page_image' => $bgFile,
        ], $letterData);

        if (empty($letter['body_paragraphs']) && ! empty($letter['body'])) {
            $letter['body_paragraphs'] = preg_split('/\r\n|\r|\n/', $letter['body']);
        }

        $pdf = Pdf::loadView('admin.letterhead.pdf', [
            'letterhead' => $letterhead,
            'letter' => $letter,
        ])
            ->setPaper($paperSize, $orientation)
            ->setWarnings(false)
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Overlay text directly into the content section of an uploaded PDF Letterhead.
     */
    protected static function generateWithFpdi(string $templatePdfPath, array $letterData, string $fileName): Response
    {
        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 35); // 35mm bottom clearance for footer
        $pdf->SetMargins(22, 48, 22); // 22mm left/right, 48mm top clearance for header

        // Import Page 1 from uploaded PDF Letterhead
        $pdf->setSourceFile($templatePdfPath);
        $tplId = $pdf->importPage(1);

        $pdf->AddPage();
        // Stamp full A4 background template (210mm x 297mm)
        $pdf->useTemplate($tplId, 0, 0, 210, 297);

        // Start text cursor at 48mm from top
        $pdf->SetY(48);

        // 1. REF NO & DATE
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor(30, 41, 59);
        $refNo = $letterData['ref_no'] ?? ('REF/' . date('Y') . '/' . strtoupper(Str::random(6)));
        $dateStr = $letterData['date'] ?? now()->format('F d, Y');

        $pdf->Cell(80, 5, 'Ref No: ' . $refNo, 0, 0, 'L');
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(86, 5, 'Date: ' . $dateStr, 0, 1, 'R');
        $pdf->Ln(4);

        // 2. RECIPIENT BLOCK
        if (! empty($letterData['recipient_name'])) {
            $pdf->SetFont('Helvetica', 'B', 9.5);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell(0, 5, 'To,', 0, 1, 'L');
            $pdf->Cell(0, 5, $letterData['recipient_name'], 0, 1, 'L');

            if (! empty($letterData['recipient_email'])) {
                $pdf->SetFont('Helvetica', '', 8.5);
                $pdf->SetTextColor(71, 85, 105);
                $pdf->Cell(0, 4.5, $letterData['recipient_email'], 0, 1, 'L');
            }
            $pdf->Ln(3);
        }

        // 3. SUBJECT LINE
        if (! empty($letterData['subject'])) {
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetTextColor(15, 116, 76); // Emerald brand color
            $pdf->MultiCell(0, 5.5, 'Subject: ' . $letterData['subject'], 0, 'L');
            $pdf->Ln(3);
        }

        // 4. LETTER BODY PARAGRAPHS
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->SetTextColor(51, 65, 85);

        $body = $letterData['body'] ?? '';
        $paragraphs = $letterData['body_paragraphs'] ?? preg_split('/\r\n|\r|\n/', $body);

        foreach ($paragraphs as $para) {
            $trimmed = trim($para);
            if ($trimmed !== '') {
                // MultiCell provides clean line wrapping with justification
                $pdf->MultiCell(0, 5.2, iconv('UTF-8', 'windows-1252//TRANSLIT', $trimmed), 0, 'J');
                $pdf->Ln(2.5);
            }
        }

        $pdf->Ln(4);

        // 5. SIGNATORY BLOCK
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->Cell(0, 5, 'Sincerely,', 0, 1, 'L');
        $pdf->Ln(10);

        // Signature line
        $currentX = $pdf->GetX();
        $currentY = $pdf->GetY();
        $pdf->Line($currentX, $currentY, $currentX + 50, $currentY);
        $pdf->Ln(2);

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(0, 4.5, $letterData['signatory_name'] ?? (auth()->user()?->name ?: 'Arthur Pendelton'), 0, 1, 'L');

        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 4, $letterData['signatory_title'] ?? 'Authorized Signatory', 0, 1, 'L');

        $output = $pdf->Output('S');

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
