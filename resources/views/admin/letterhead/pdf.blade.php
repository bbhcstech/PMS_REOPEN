<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    @php
        $doc = $letter ?? ($sampleLetter ?? []);
        $paragraphs = $doc['body_paragraphs'] ?? (isset($doc['body']) ? preg_split('/\r\n|\r|\n/', $doc['body']) : []);

        // Layout mode and image assets (relative public paths or absolute fitted files)
        $layoutMode = $doc['layout_mode'] ?? ($letterhead?->layout_mode ?: 'custom_header_footer');
        $fullPageImage = $doc['background_page_image'] ?? ($letterhead?->background_page_image ?? null);
        $headerImage = $doc['header_image'] ?? ($letterhead?->header_image ?? null);
        $footerImage = $doc['footer_image'] ?? ($letterhead?->footer_image ?? null);

        // Fallbacks to the default presets if nothing was provided
        if ($layoutMode === 'full_a4_page' && !$fullPageImage) {
            $fullPageImage = 'assets/letterhead/presets/bengal_it_hub_a4.svg';
        }
        if (!$headerImage && $layoutMode !== 'full_a4_page') {
            $headerImage = 'assets/letterhead/presets/bengal_header.svg';
        }
        if (!$footerImage && $layoutMode !== 'full_a4_page') {
            $footerImage = 'assets/letterhead/presets/bengal_footer.svg';
        }

        $fullPageFile = $layoutMode === 'full_a4_page' ? \App\Services\LetterheadImage::absolute($fullPageImage) : null;
        $headerFile = $fullPageFile ? null : \App\Services\LetterheadImage::absolute($headerImage);
        $footerFile = $fullPageFile ? null : \App\Services\LetterheadImage::absolute($footerImage);

        // Fixed slot sizes on A4 (210mm wide): header 1240x160 px, footer 1240x140 px.
        $headerHeight = round(210 * \App\Services\LetterheadImage::HEADER[1] / \App\Services\LetterheadImage::HEADER[0], 2); // 27.1mm
        $footerHeight = round(210 * \App\Services\LetterheadImage::FOOTER[1] / \App\Services\LetterheadImage::FOOTER[0], 2); // 23.71mm

        // Page margins reserve the header/footer on EVERY page, so text never runs under them and
        // only as many pages as the content needs are produced.
        $marginTop = $fullPageFile ? 42 : ($headerFile ? $headerHeight + 8 : 20);
        $marginBottom = $fullPageFile ? 34 : ($footerFile ? $footerHeight + 8 : 20);

        // Raster images are embedded (works for temporary fitted copies outside the PDF engine's file root).
        $imageSrc = function (?string $file) {
            if (! $file) return null;
            $info = @getimagesize($file);
            if (is_array($info) && ! empty($info['mime'])) return 'data:' . $info['mime'] . ';base64,' . base64_encode((string) file_get_contents($file));
            return $file;
        };
        $accent = $letterhead?->primary_color ?: '#2F6BFF';
        $companyName = $letterhead?->company_name ?: 'Bengal IT Hub Private Limited';
    @endphp
    <title>{{ $doc['subject'] ?? ($letterhead?->name ?? 'Official Document') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: {{ $marginTop }}mm 0 {{ $marginBottom }}mm 0;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 9.5pt;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* Header / footer live in the page margins and repeat on every page. */
        .lh-header {
            position: fixed;
            top: -{{ $marginTop }}mm;
            left: 0;
            right: 0;
            height: {{ $headerHeight }}mm;
            overflow: hidden;
        }
        .lh-footer {
            position: fixed;
            bottom: -{{ $marginBottom }}mm;
            left: 0;
            right: 0;
            height: {{ $footerHeight }}mm;
            overflow: hidden;
        }
        .lh-header img, .lh-footer img {
            display: block;
            width: 210mm;
        }
        .lh-header img { height: {{ $headerHeight }}mm; }
        .lh-footer img { height: {{ $footerHeight }}mm; }

        .bg-full-page {
            position: fixed;
            top: -{{ $marginTop }}mm;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: -1000;
        }

        @if($letterhead && $letterhead->watermark_enabled && $letterhead->watermark_text && !$fullPageFile)
        .watermark-container {
            position: fixed;
            top: 38%;
            left: 10%;
            width: 80%;
            text-align: center;
            opacity: {{ $letterhead->watermark_opacity ?: 0.07 }};
            transform: rotate({{ $letterhead->watermark_rotation ?: -45 }}deg);
            z-index: -500;
        }
        .watermark-text {
            font-size: {{ $letterhead->watermark_size ?: 44 }}pt;
            font-weight: bold;
            color: {{ $accent }};
            text-transform: uppercase;
            letter-spacing: 6px;
        }
        @endif

        .content-wrap {
            padding: 0 20mm;
        }

        .doc-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 6mm 0;
        }
        .doc-meta-table td {
            font-size: 8.5pt;
            vertical-align: top;
            padding: 0;
        }
        .doc-label {
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-size: 7pt;
            font-weight: bold;
        }
        .doc-value {
            color: #0f172a;
            font-weight: bold;
            font-size: 9pt;
        }
        .doc-date { text-align: right; }

        .recipient-box {
            margin: 0 0 6mm 0;
            font-size: 9.5pt;
            line-height: 1.5;
        }
        .recipient-to {
            color: #64748b;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: bold;
        }
        .recipient-name {
            font-weight: bold;
            color: #0f172a;
            font-size: 10.5pt;
        }
        .recipient-line { color: #475569; }

        .doc-subject {
            margin: 0 0 6mm 0;
            padding: 3mm 4mm;
            border-left: 3px solid {{ $accent }};
            background: #f1f5ff;
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
        }
        .doc-subject span { color: {{ $accent }}; }

        .doc-body p {
            margin: 0 0 4mm 0;
            text-align: justify;
            font-size: 9.5pt;
            line-height: 1.65;
            color: #334155;
        }

        .signature-section {
            margin-top: 10mm;
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .sig-block { width: 60%; vertical-align: bottom; padding: 0; }
        .seal-block { width: 40%; vertical-align: bottom; text-align: right; padding: 0; }
        .sig-closing { color: #475569; font-size: 9pt; margin-bottom: 12mm; }
        .sig-line { border-top: 1px solid #94a3b8; width: 60mm; margin-bottom: 2mm; }
        .sig-name { font-size: 10pt; font-weight: bold; color: #0f172a; }
        .sig-title { font-size: 8.5pt; color: #64748b; }
        .seal {
            display: inline-block;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 3mm 5mm;
            font-size: 7pt;
            color: #94a3b8;
            letter-spacing: 0.8px;
        }
    </style>
</head>
<body>

    @if($fullPageFile)
        <img src="{{ $imageSrc($fullPageFile) }}" class="bg-full-page" alt="">
    @endif

    @if($headerFile)
        <div class="lh-header"><img src="{{ $imageSrc($headerFile) }}" alt=""></div>
    @endif

    @if($footerFile)
        <div class="lh-footer"><img src="{{ $imageSrc($footerFile) }}" alt=""></div>
    @endif

    @if($letterhead && $letterhead->watermark_enabled && $letterhead->watermark_text && !$fullPageFile)
        <div class="watermark-container"><div class="watermark-text">{{ $letterhead->watermark_text }}</div></div>
    @endif

    <div class="content-wrap">
        <table class="doc-meta-table">
            <tr>
                <td>
                    <div class="doc-label">Reference No.</div>
                    <div class="doc-value">{{ $doc['ref_no'] ?? ('REF/' . date('Y') . '/IT-0842') }}</div>
                </td>
                <td class="doc-date">
                    <div class="doc-label">Date</div>
                    <div class="doc-value">{{ $doc['date'] ?? now()->format('F d, Y') }}</div>
                </td>
            </tr>
        </table>

        @if(!empty($doc['recipient_name']))
            <div class="recipient-box">
                <div class="recipient-to">To</div>
                <div class="recipient-name">{{ $doc['recipient_name'] }}</div>
                @if(!empty($doc['recipient_email']))<div class="recipient-line">{{ $doc['recipient_email'] }}</div>@endif
                @if(!empty($doc['recipient_org']))<div class="recipient-line">{{ $doc['recipient_org'] }}</div>@endif
                @if(!empty($doc['recipient_address']))<div class="recipient-line">{{ $doc['recipient_address'] }}</div>@endif
            </div>
        @endif

        @if(!empty($doc['subject']))
            <div class="doc-subject"><span>Subject:</span> {{ $doc['subject'] }}</div>
        @endif

        <div class="doc-body">
            @if(count($paragraphs) > 0)
                @foreach($paragraphs as $para)
                    @php $trimmed = trim($para); @endphp
                    @if($trimmed !== '')
                        <p>{{ $trimmed }}</p>
                    @endif
                @endforeach
            @elseif(!empty($doc['body']))
                <p>{!! nl2br(e($doc['body'])) !!}</p>
            @endif
        </div>

        <table class="signature-section">
            <tr>
                <td class="sig-block">
                    <div class="sig-closing">Sincerely,</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $doc['signatory_name'] ?? (auth()->user()?->name ?: 'Authorized Signatory') }}</div>
                    <div class="sig-title">{{ $doc['signatory_title'] ?? 'Authorized Signatory' }}</div>
                    <div class="sig-title">{{ $companyName }}</div>
                </td>
                <td class="seal-block">
                    <div class="seal">OFFICIAL SEAL</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
