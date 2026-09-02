<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Letter Head - {{ $letterhead->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    @php
        $layoutMode = $letterhead->layout_mode ?: 'custom_header_footer';
        $fullPageImage = $letterhead->background_page_image ?: 'assets/letterhead/presets/bengal_it_hub_a4.svg';
        $headerImage = $letterhead->header_image ?: 'assets/letterhead/presets/bengal_header.svg';
        $footerImage = $letterhead->footer_image ?: 'assets/letterhead/presets/bengal_footer.svg';

        $hasFullBg = ($layoutMode === 'full_a4_page' && file_exists(public_path($fullPageImage)));
        $hasHeaderImg = ($headerImage && file_exists(public_path($headerImage)));
        $hasFooterImg = ($footerImage && file_exists(public_path($footerImage)));
    @endphp
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            padding: 2rem 1rem;
            display: flex;
            justify-content: center;
        }

        .print-toolbar {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            display: flex;
            gap: 0.75rem;
            background: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .btn-action {
            padding: 0.5rem 1.25rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.88rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-print {
            background: #0f744c;
            color: #ffffff;
        }

        .btn-close {
            background: #e2e8f0;
            color: #475569;
        }

        .paper-sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .header-img-banner {
            width: 100%;
            height: 125px;
            object-fit: fill;
            display: block;
            flex-shrink: 0;
        }

        .footer-img-banner {
            width: 100%;
            height: 105px;
            object-fit: fill;
            display: block;
            margin-top: auto;
            flex-shrink: 0;
        }

        /* WATERMARK */
        @if($letterhead->watermark_enabled)
        .watermark-overlay {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate({{ $letterhead->watermark_rotation ?: -45 }}deg);
            opacity: {{ $letterhead->watermark_opacity ?: 0.08 }};
            font-size: {{ $letterhead->watermark_size ?: 48 }}pt;
            font-weight: 900;
            color: {{ $letterhead->primary_color ?: '#0f744c' }};
            text-transform: uppercase;
            letter-spacing: 6px;
            pointer-events: none;
            z-index: 1;
            white-space: nowrap;
        }
        @endif

        /* BODY */
        .lh-body-block {
            flex: 1;
            font-size: 10pt;
            line-height: 1.6;
            color: #334155;
            padding: 20px 40px;
            z-index: 2;
        }

        .meta-line {
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 1.2rem;
        }

        .recipient-box {
            margin-bottom: 1.2rem;
            font-size: 9.5pt;
            line-height: 1.45;
        }

        .recipient-box strong {
            color: #0f172a;
            font-size: 10pt;
        }

        .subject-line {
            font-size: 10.5pt;
            font-weight: 800;
            color: #0f744c;
            text-decoration: underline;
            margin: 1rem 0 1rem;
        }

        .lh-body-block p {
            margin-bottom: 1rem;
            text-align: justify;
        }

        /* SIGNATURE */
        .signature-block {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .sig-line {
            width: 180px;
            border-top: 1px solid #94a3b8;
            margin-top: 30px;
            margin-bottom: 4px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-toolbar {
                display: none;
            }
            .paper-sheet {
                box-shadow: none;
                width: 100%;
                min-height: 100vh;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="print-toolbar">
        <button class="btn-action btn-print" onclick="window.print()">🖨️ Print Now</button>
        <button class="btn-action btn-close" onclick="window.close()">✕ Close</button>
    </div>

    <div class="paper-sheet">
        @if($letterhead->watermark_enabled && $letterhead->watermark_text && !$hasFullBg)
            <div class="watermark-overlay">{{ $letterhead->watermark_text }}</div>
        @endif

        <!-- 1. PLACED HEADER IMAGE -->
        @if($hasHeaderImg && !$hasFullBg)
            <img src="{{ asset($headerImage) }}" class="header-img-banner" alt="Header Image">
        @endif

        <!-- 2. BODY CONTENT (FLOATS IN MIDDLE) -->
        <div class="lh-body-block" style="{{ $hasFullBg ? 'padding-top: 140px; padding-bottom: 120px;' : '' }}">
            <div class="meta-line">
                <span>Ref: {{ $sampleLetter['ref_no'] }}</span>
                <span>Date: {{ $sampleLetter['date'] }}</span>
            </div>

            <div class="recipient-box">
                <strong>To,</strong><br>
                <strong>{{ $sampleLetter['recipient_name'] }}</strong><br>
                {{ $sampleLetter['recipient_title'] }}<br>
                {{ $sampleLetter['recipient_org'] }}<br>
                {{ $sampleLetter['recipient_address'] }}
            </div>

            <div class="subject-line">Subject: {{ $sampleLetter['subject'] }}</div>

            <p>Dear {{ $sampleLetter['recipient_name'] }},</p>

            @foreach($sampleLetter['body_paragraphs'] as $para)
                <p>{{ $para }}</p>
            @endforeach

            <p>Sincerely,</p>

            <div class="signature-block">
                <div>
                    <div class="sig-line"></div>
                    <strong>{{ $sampleLetter['signatory_name'] }}</strong><br>
                    <small style="color: #64748b;">{{ $sampleLetter['signatory_title'] }}</small><br>
                    <small style="color: #64748b;">{{ $letterhead->company_name ?: 'Bengal IT Hub Private Limited' }}</small>
                </div>
                <div style="border: 1px dashed #cbd5e1; border-radius: 6px; padding: 10px 16px; font-size: 8pt; color: #94a3b8; text-align: center;">
                    [ OFFICIAL CORPORATE SEAL ]
                </div>
            </div>
        </div>

        <!-- 3. PLACED FOOTER IMAGE -->
        @if($hasFooterImg && !$hasFullBg)
            <img src="{{ asset($footerImage) }}" class="footer-img-banner" alt="Footer Image">
        @endif
    </div>

</body>
</html>
