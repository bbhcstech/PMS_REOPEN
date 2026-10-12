<?php

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class PdfLetterheadService
{
    public static function generate(array $letterData, ?object $letterhead = null, string $fileName = 'Letter.pdf'): Response
    {
        $letter = array_merge([
            'ref_no' => 'REF/'.date('Y').'/'.strtoupper(Str::random(6)),
            'date' => now()->format('F d, Y'),
            'recipient_name' => '',
            'recipient_email' => '',
            'subject' => 'Official Letter',
            'body' => '',
            'body_paragraphs' => [],
            'signatory_name' => auth()->user()?->name ?: 'Authorized Signatory',
            'signatory_title' => 'Authorized Signatory',
            'layout_mode' => $letterhead?->layout_mode ?: 'custom_header_footer',
            'header_image' => $letterhead?->header_image,
            'footer_image' => $letterhead?->footer_image,
            'background_page_image' => $letterhead?->background_page_image,
        ], $letterData);
        if (empty($letter['body_paragraphs']) && ! empty($letter['body'])) {
            $letter['body_paragraphs'] = preg_split('/\r\n|\r|\n/', $letter['body']);
        }

        $pdf = new class extends Fpdi {
            public function clipBand(float $x, float $y, float $width, float $height): void
            {
                $this->_out(sprintf('q %.3F %.3F %.3F %.3F re W n', $x * $this->k, ($this->h - $y - $height) * $this->k, $width * $this->k, $height * $this->k));
            }

            public function finishBand(): void
            {
                $this->_out('Q');
            }
        };
        $artwork = [];
        $slots = $letter['layout_mode'] === 'full_a4_page' ? ['background_page_image'] : ['header_image', 'footer_image'];
        foreach ($slots as $slot) {
            $path = LetterheadImage::absolute($letter[$slot]);
            if ($path && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
                try {
                    $pdf->setSourceFile($path);
                    $artwork[$slot] = $pdf->importPage(1);
                } catch (\Throwable $e) {
                    throw ValidationException::withMessages([$slot => 'This PDF artwork cannot be imported. Upload an unencrypted PDF saved without compressed object streams, or use a PNG/JPEG image.']);
                }
            }
        }
        // HTML performs all text layout (including Unicode); FPDI only adds PDF artwork.
        $letter['imported_artwork'] = array_keys($artwork);
        $paper = $letterhead?->paper_size ?: 'a4';
        $orientation = $letterhead?->orientation ?: 'portrait';
        $paperBox = \Dompdf\Adapter\CPDF::$PAPER_SIZES[strtolower($paper)] ?? \Dompdf\Adapter\CPDF::$PAPER_SIZES['a4'];
        $pageWidth = ($paperBox[2] - $paperBox[0]) * 25.4 / 72;
        $pageHeight = ($paperBox[3] - $paperBox[1]) * 25.4 / 72;
        if ($orientation === 'landscape') {
            [$pageWidth, $pageHeight] = [$pageHeight, $pageWidth];
        }
        $htmlPdf = PdfDocument::loadView('admin.letterhead.pdf', compact('letterhead', 'letter', 'pageWidth', 'pageHeight'), [], [
            'footer_offset_mm' => $letter['layout_mode'] === 'full_a4_page' ? 29 : $pageWidth * LetterheadImage::FOOTER[1] / LetterheadImage::FOOTER[0] + 5,
        ])->setPaper($paper, $orientation);
        $output = $htmlPdf->output();

        if ($artwork) {
            $pageCount = $pdf->setSourceFile(StreamReader::createByString($output));
            $pdf->SetAutoPageBreak(false);
            for ($page = 1; $page <= $pageCount; $page++) {
                $content = $pdf->importPage($page);
                $size = $pdf->getTemplateSize($content);
                $width = $size['width'];
                $height = $size['height'];
                $pdf->AddPage($size['orientation'], [$width, $height]);
                if (isset($artwork['background_page_image'])) {
                    $pdf->useTemplate($artwork['background_page_image'], 0, 0, $width, $height);
                }
                if (isset($artwork['header_image'])) {
                    $band = $width * LetterheadImage::HEADER[1] / LetterheadImage::HEADER[0];
                    $pdf->clipBand(0, 0, $width, $band);
                    $pdf->useTemplate($artwork['header_image'], 0, 0, $width);
                    $pdf->finishBand();
                }
                if (isset($artwork['footer_image'])) {
                    $band = $width * LetterheadImage::FOOTER[1] / LetterheadImage::FOOTER[0];
                    $artSize = $pdf->getTemplateSize($artwork['footer_image'], $width);
                    $pdf->clipBand(0, $height - $band, $width, $band);
                    $pdf->useTemplate($artwork['footer_image'], 0, $height - $artSize['height'], $width);
                    $pdf->finishBand();
                }
                $pdf->useTemplate($content, 0, 0, $width, $height);
            }
            $output = $pdf->Output('S');
        }

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.str_replace(['"', "\r", "\n"], '', $fileName).'"',
        ]);
    }
}
