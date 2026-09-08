<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    @php
        $doc = $letter ?? ($sampleLetter ?? []);
        $paragraphs = $doc['body_paragraphs'] ?? (isset($doc['body']) ? preg_split('/\r\n|\r|\n/', $doc['body']) : []);
        
        // Determine layout mode and image assets
        $layoutMode = $doc['layout_mode'] ?? ($letterhead?->layout_mode ?: 'custom_header_footer');
        $fullPageImage = $doc['background_page_image'] ?? ($letterhead?->background_page_image ?? null);
        $headerImage = $doc['header_image'] ?? ($letterhead?->header_image ?? null);
        $footerImage = $doc['footer_image'] ?? ($letterhead?->footer_image ?? null);

        // Fallbacks to default Bengal IT Hub presets if not explicitly provided
        if ($layoutMode === 'full_a4_page' && !$fullPageImage) {
            $fullPageImage = 'assets/letterhead/presets/bengal_it_hub_a4.svg';
        }
        if (!$headerImage && $layoutMode !== 'full_a4_page') {
            $headerImage = 'assets/letterhead/presets/bengal_header.svg';
        }
        if (!$footerImage && $layoutMode !== 'full_a4_page') {
            $footerImage = 'assets/letterhead/presets/bengal_footer.svg';
        }

        $hasFullBg = ($layoutMode === 'full_a4_page' && $fullPageImage && file_exists(public_path($fullPageImage)));
        $hasHeaderImg = ($headerImage && file_exists(public_path($headerImage)));
        $hasFooterImg = ($footerImage && file_exists(public_path($footerImage)));
    @endphp
    <title>{{ $doc['subject'] ?? ($letterhead?->name ?? 'Official Document') }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.55;
            margin: 0;
            padding: 0;
        }

        /* FULL A4 PAGE BACKGROUND IMAGE */
        @if($hasFullBg)
            .bg-full-page {
                position: fixed;
                top: 0;
                left: 0;
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 0;
                z-index: -1000;
            }
        @endif

        /* HEADER IMAGE ONLY - FIXED SIZE */
        @if(!$hasFullBg && $hasHeaderImg)
            .lh-header-image-container {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                width: 100%;
                height: 125px;
                overflow: hidden;
                text-align: center;
                z-index: 100;
            }
            .lh-header-image-container img {
                width: 100%;
                height: 125px;
                display: block;
            }
        @endif

        /* FOOTER IMAGE ONLY - FIXED SIZE */
        @if(!$hasFullBg && $hasFooterImg)
            .lh-footer-image-container {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                height: 105px;
                overflow: hidden;
                text-align: center;
                z-index: 100;
            }
            .lh-footer-image-container img {
                width: 100%;
                height: 105px;
                display: block;
            }
        @endif

        /* CONTENT CONTAINER WITH DYNAMIC CLEARANCE PADDING */
        .content-wrap {
            padding-top: {{ $hasFullBg ? 140 : ($hasHeaderImg ? 145 : 40) }}px;
            padding-bottom: {{ $hasFullBg ? 120 : ($hasFooterImg ? 125 : 40) }}px;
            padding-left: 65px;
            padding-right: 65px;
        }

        /* WATERMARK */
        @if($letterhead && $letterhead->watermark_enabled && $letterhead->watermark_text && !$hasFullBg)
        .watermark-container {
            position: fixed;
            top: 40%;
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
            color: {{ $letterhead->primary_color ?: '#0f744c' }};
            text-transform: uppercase;
            letter-spacing: 6px;
        }
        @endif

        /* DOCUMENT METADATA */
        .doc-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 14px;
        }

        .doc-meta-table td {
            font-size: 9pt;
            vertical-align: top;
        }

        .doc-ref {
            font-weight: bold;
            color: #0f172a;
        }

        .doc-date {
            text-align: right;
            color: #475569;
            font-weight: 600;
        }

        .recipient-box {
            margin-bottom: 16px;
            font-size: 9.5pt;
            line-height: 1.4;
        }

        .recipient-name {
            font-weight: bold;
            color: #0f172a;
        }

        .doc-subject {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f744c;
            margin: 12px 0 14px 0;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .doc-body p {
            margin-bottom: 12px;
            text-align: justify;
            font-size: 9.5pt;
            line-height: 1.55;
            color: #334155;
        }

        /* SIGNATURE SECTION */
        .signature-section {
            margin-top: 24px;
            width: 100%;
            border-collapse: collapse;
        }

        .sig-block {
            width: 50%;
            vertical-align: top;
        }

        .sig-space {
            height: 30px;
        }

        .sig-line {
            border-top: 1px solid #94a3b8;
            width: 170px;
            margin-top: 6px;
            margin-bottom: 3px;
        }

        .sig-name {
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
        }

        .sig-title {
            font-size: 8pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- 1. FULL A4 PAGE BACKGROUND IMAGE -->
    @if($hasFullBg)
        <img src="{{ public_path($fullPageImage) }}" class="bg-full-page">
    @endif

    <!-- 2. HEADER IMAGE (Placed at Header Position - Fixed Size) -->
    @if(!$hasFullBg && $hasHeaderImg)
        <div class="lh-header-image-container">
            <img src="{{ public_path($headerImage) }}" width="100%" height="125">
        </div>
    @endif

    <!-- 3. FOOTER IMAGE (Placed at Footer Position - Fixed Size) -->
    @if(!$hasFullBg && $hasFooterImg)
        <div class="lh-footer-image-container">
            <img src="{{ public_path($footerImage) }}" width="100%" height="105">
        </div>
    @endif

    <!-- 4. WATERMARK -->
    @if($letterhead && $letterhead->watermark_enabled && $letterhead->watermark_text && !$hasFullBg)
    <div class="watermark-container">
        <div class="watermark-text">{{ $letterhead->watermark_text }}</div>
    </div>
    @endif

    <!-- 5. MAIN CONTENT SECTION (Floats clean in between Header & Footer) -->
    <div class="content-wrap">
        
        <!-- REFERENCE NUMBER & DATE -->
        <table class="doc-meta-table">
            <tr>
                <td class="doc-ref">Ref No: {{ $doc['ref_no'] ?? ('REF/' . date('Y') . '/IT-0842') }}</td>
                <td class="doc-date">Date: {{ $doc['date'] ?? now()->format('F d, Y') }}</td>
            </tr>
        </table>

        <!-- RECIPIENT -->
        @if(!empty($doc['recipient_name']))
        <div class="recipient-box">
            <div><strong>To,</strong></div>
            <div class="recipient-name">{{ $doc['recipient_name'] }}</div>
            @if(!empty($doc['recipient_email']))
                <div>{{ $doc['recipient_email'] }}</div>
            @endif
            @if(!empty($doc['recipient_org']))
                <div>{{ $doc['recipient_org'] }}</div>
            @endif
            @if(!empty($doc['recipient_address']))
                <div>{{ $doc['recipient_address'] }}</div>
            @endif
        </div>
        @endif

        <!-- SUBJECT LINE -->
        @if(!empty($doc['subject']))
        <div class="doc-subject">Subject: {{ $doc['subject'] }}</div>
        @endif

        <!-- LETTER BODY CONTENT -->
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

        <!-- SIGNATURE BLOCK -->
        <table class="signature-section">
            <tr>
                <td class="sig-block">
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $doc['signatory_name'] ?? (auth()->user()?->name ?: 'Arthur Pendelton') }}</div>
                    <div class="sig-title">{{ $doc['signatory_title'] ?? 'Authorized Signatory' }}</div>
                    <div class="sig-title">{{ $letterhead?->company_name ?: 'Bengal IT Hub Private Limited' }}</div>
                </td>
                <td class="sig-block" style="text-align: right;">
                    <div class="sig-space"></div>
                    <div style="display: inline-block; text-align: center; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 8px 14px; font-size: 7.5pt; color: #94a3b8;">
                        [ OFFICIAL SEAL ]
                    </div>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
