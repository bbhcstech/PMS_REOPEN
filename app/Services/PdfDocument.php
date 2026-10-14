<?php

namespace App\Services;

use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\File;

/** One configured renderer for reports, payslips and corporate letters. */
class PdfDocument
{
    /** Embed local artwork; flatten unsupported SVG gradients without modifying the original. */
    public static function imageSource(string $file): string
    {
        $bytes = (string) file_get_contents($file);
        $image = @getimagesize($file);
        if (is_array($image) && ! empty($image['mime'])) {
            return 'data:'.$image['mime'].';base64,'.base64_encode($bytes);
        }
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'svg') return $file;
        $bytes = str_replace('&nbsp;', '&#160;', $bytes);
        $previous = libxml_use_internal_errors(true);
        $svg = new \DOMDocument;
        $loaded = $svg->loadXML($bytes, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (! $loaded) return $file;
        $xpath = new \DOMXPath($svg);
        $xpath->registerNamespace('svg', 'http://www.w3.org/2000/svg');
        $colors = [];
        foreach ($xpath->query('//svg:linearGradient | //svg:radialGradient') as $gradient) {
            $stops = $gradient->getElementsByTagName('stop');
            if ($stops->length) {
                $color = $stops->item(0)->getAttribute('stop-color');
                if (preg_match('/^#[0-9a-f]{3,8}$/i', $color)) $colors['url(#'.$gradient->getAttribute('id').')'] = $color;
            }
        }
        foreach ($xpath->query('//*[@font-family]') as $element) {
            $element->setAttribute('font-family', 'DejaVu Sans');
        }
        return 'data:image/svg+xml;base64,'.base64_encode(strtr($svg->saveXML(), $colors));
    }

    public static function loadView(string $view, array $data = [], array $mergeData = [], array $options = []): PDF
    {
        foreach (['font_dir', 'font_cache', 'temp_dir'] as $directory) {
            File::ensureDirectoryExists(config('dompdf.options.'.$directory), 0755, true);
        }

        // Resolve a fresh instance: a reused Dompdf instance can retain pages and callbacks.
        $pdf = app('dompdf.wrapper');
        $pdf->loadView($view, $data, $mergeData);
        $footerOffset = (float) ($options['footer_offset_mm'] ?? 6);
        if ($view === 'admin.recruitment.pdf' && ! empty($data['requirement']->pdf_footer_image)) {
            $footerOffset = 27;
        }
        $pdf->getDomPDF()->setCallbacks([[
            'event' => 'end_document',
            'f' => static function ($page, $pages, $canvas, $fonts) use ($footerOffset) {
                $font = $fonts->getFont('DejaVu Sans', 'normal');
                $text = "Page {$page} of {$pages}";
                $width = $fonts->getTextWidth($text, $font, 7);
                $canvas->text($canvas->get_width() - $width - 28, $canvas->get_height() - $footerOffset * 72 / 25.4, $text, $font, 7, [0.35, 0.40, 0.48]);
            },
        ]]);

        return $pdf;
    }
}
