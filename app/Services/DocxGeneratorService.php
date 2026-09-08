<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class DocxGeneratorService
{
    /**
     * Generate a professional Microsoft Word (.docx) document.
     */
    public static function createDocument(array $data, ?string $outputPath = null): string
    {
        if (! class_exists('ZipArchive')) {
            throw new \RuntimeException('ZipArchive extension is required for DOCX generation.');
        }

        $tempDir = storage_path('app/temp_docx');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $filename = 'Letter_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6) . '.docx';
        $fullPath = $outputPath ?: ($tempDir . '/' . $filename);

        $zip = new ZipArchive();
        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create temporary DOCX file at ' . $fullPath);
        }

        // Add standard OpenXML package structures
        $zip->addFromString('[Content_Types].xml', self::getContentTypesXml());
        $zip->addFromString('_rels/.rels', self::getRelsXml());
        $zip->addFromString('word/_rels/document.xml.rels', self::getDocumentRelsXml());
        $zip->addFromString('word/styles.xml', self::getStylesXml());
        $zip->addFromString('word/fontTable.xml', self::getFontTableXml());
        $zip->addFromString('word/settings.xml', self::getSettingsXml());
        $zip->addFromString('word/document.xml', self::getDocumentXml($data));

        $zip->close();

        return $fullPath;
    }

    /**
     * Download as Word document response.
     */
    public static function download(array $data, string $downloadName = 'Official_Letter.docx'): BinaryFileResponse
    {
        $filePath = self::createDocument($data);

        return response()->download($filePath, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Escape XML special characters.
     */
    private static function xmlEscape(?string $string): string
    {
        return htmlspecialchars($string ?? '', ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    /**
     * Build the primary document.xml containing formatted letter content.
     */
    private static function getDocumentXml(array $data): string
    {
        $companyName = self::xmlEscape($data['company_name'] ?? 'BENGAL IT HUB PRIVATE LIMITED');
        $cinNumber = self::xmlEscape($data['cin_number'] ?? 'CIN : U62090WB2026PTC287230');
        $address = self::xmlEscape($data['address'] ?? '3RD FLOOR 259, NEW SANTOSHPUR MAIN RD, SANTOSHPUR, KOLKATA 700075, INDIA');
        $phone = self::xmlEscape($data['phone'] ?? '+91 92306 53975');
        $email = self::xmlEscape($data['email'] ?? 'CONTACT@BENGALITHUB.COM');
        $website = self::xmlEscape($data['website'] ?? 'WWW.BENGALITHUB.COM');
        $subject = self::xmlEscape($data['subject'] ?? 'Official Communication');
        $refNo = self::xmlEscape($data['ref_no'] ?? ('REF/' . date('Y') . '/' . strtoupper(substr(md5(time()), 0, 6))));
        $date = self::xmlEscape($data['date'] ?? now()->format('F d, Y'));
        $recipientName = self::xmlEscape($data['recipient_name'] ?? '');
        $signatoryName = self::xmlEscape($data['signatory_name'] ?? 'Arthur Pendelton');
        $signatoryTitle = self::xmlEscape($data['signatory_title'] ?? 'Authorized Signatory');

        $rawBody = $data['body'] ?? '';
        $paragraphs = preg_split('/\r\n|\r|\n/', $rawBody);

        $bodyXml = '';

        // 1. HEADER BANNER SECTION
        $bodyXml .= '<w:p w:rsidR="00000000" w:rsidRDefault="00000000">';
        $bodyXml .= '<w:pPr><w:pBdr><w:bottom w:val="single" w:sz="18" w:space="8" w:color="1A56DB"/></w:pBdr><w:spacing w:after="160"/></w:pPr>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:b/><w:sz w:val="32"/><w:color w:val="1E293B"/></w:rPr><w:t>' . $companyName . '</w:t></w:r>';
        if ($cinNumber) {
            $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="18"/><w:color w:val="64748B"/></w:rPr><w:tab/><w:t>' . $cinNumber . '</w:t></w:r>';
        }
        $bodyXml .= '</w:p>';

        // 2. REFERENCE & DATE ROW
        $bodyXml .= '<w:p>';
        $bodyXml .= '<w:pPr><w:tabs><w:tab w:val="right" w:pos="9360"/></w:tabs><w:spacing w:after="240"/></w:pPr>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:sz w:val="20"/><w:color w:val="334155"/></w:rPr><w:t>Ref No: ' . $refNo . '</w:t></w:r>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:sz w:val="20"/><w:color w:val="334155"/></w:rPr><w:tab/><w:t>Date: ' . $date . '</w:t></w:r>';
        $bodyXml .= '</w:p>';

        // 3. RECIPIENT BLOCK (If present)
        if ($recipientName) {
            $bodyXml .= '<w:p><w:pPr><w:spacing w:after="80"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:sz w:val="22"/><w:color w:val="0F172A"/></w:rPr><w:t>To,</w:t></w:r></w:p>';
            $bodyXml .= '<w:p><w:pPr><w:spacing w:after="200"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:sz w:val="22"/><w:color w:val="0F172A"/></w:rPr><w:t>' . $recipientName . '</w:t></w:r></w:p>';
        }

        // 4. SUBJECT LINE
        if ($subject) {
            $bodyXml .= '<w:p>';
            $bodyXml .= '<w:pPr><w:spacing w:before="120" w:after="280"/><w:jc w:val="left"/></w:pPr>';
            $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:u w:val="single"/><w:sz w:val="23"/><w:color w:val="0F744C"/></w:rPr><w:t>Subject: ' . $subject . '</w:t></w:r>';
            $bodyXml .= '</w:p>';
        }

        // 5. BODY PARAGRAPHS
        foreach ($paragraphs as $para) {
            $trimmed = trim($para);
            if ($trimmed === '') {
                $bodyXml .= '<w:p><w:pPr><w:spacing w:after="120"/></w:pPr></w:p>';
                continue;
            }

            // Detect list items
            $isList = preg_match('/^(\d+[\.\)]|\-|\*|•)\s+(.*)$/u', $trimmed, $matches);

            $bodyXml .= '<w:p>';
            $bodyXml .= '<w:pPr>';
            if ($isList) {
                $bodyXml .= '<w:ind w:left="480" w:hanging="240"/>';
            }
            $bodyXml .= '<w:spacing w:after="160" w:line="320" w:lineRule="auto"/>';
            $bodyXml .= '<w:jc w:val="both"/>';
            $bodyXml .= '</w:pPr>';

            // Check if strong / heading
            $isHeading = preg_match('/^(Dear|Sincerely|Warm regards|Regards|Best regards|TO WHOMSOEVER|CERTIFICATE|EMPLOYEE NON-DISCLOSURE|Ref:|Date:)/i', $trimmed);

            $bodyXml .= '<w:r>';
            $bodyXml .= '<w:rPr>';
            $bodyXml .= '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>';
            if ($isHeading) {
                $bodyXml .= '<w:b/>';
            }
            $bodyXml .= '<w:sz w:val="22"/>';
            $bodyXml .= '<w:color w:val="1E293B"/>';
            $bodyXml .= '</w:rPr>';
            $bodyXml .= '<w:t xml:space="preserve">' . self::xmlEscape($trimmed) . '</w:t>';
            $bodyXml .= '</w:r>';
            $bodyXml .= '</w:p>';
        }

        // 6. SIGNATORY SEAL & SIGNATURE BLOCK
        $bodyXml .= '<w:p><w:pPr><w:spacing w:before="360" w:after="80"/></w:pPr>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:sz w:val="22"/><w:color w:val="0F172A"/></w:rPr><w:t>For ' . $companyName . ',</w:t></w:r>';
        $bodyXml .= '</w:p>';

        $bodyXml .= '<w:p><w:pPr><w:spacing w:before="400" w:after="40"/></w:pPr>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:sz w:val="20"/><w:color w:val="94A3B8"/></w:rPr><w:t>____________________________________</w:t></w:r>';
        $bodyXml .= '</w:p>';

        $bodyXml .= '<w:p><w:pPr><w:spacing w:after="40"/></w:pPr>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:sz w:val="22"/><w:color w:val="0F172A"/></w:rPr><w:t>' . $signatoryName . '</w:t></w:r>';
        $bodyXml .= '</w:p>';

        $bodyXml .= '<w:p><w:pPr><w:spacing w:after="300"/></w:pPr>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:sz w:val="20"/><w:color w:val="64748B"/></w:rPr><w:t>' . $signatoryTitle . '</w:t></w:r>';
        $bodyXml .= '</w:p>';

        // 7. FOOTER STRIP
        $bodyXml .= '<w:p>';
        $bodyXml .= '<w:pPr><w:pBdr><w:top w:val="single" w:sz="8" w:space="8" w:color="CBD5E1"/></w:pBdr><w:spacing w:before="400" w:after="60"/><w:jc w:val="center"/></w:pPr>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:sz w:val="17"/><w:color w:val="64748B"/></w:rPr><w:t>' . $address . '</w:t></w:r>';
        $bodyXml .= '</w:p>';

        $bodyXml .= '<w:p>';
        $bodyXml .= '<w:pPr><w:spacing w:after="0"/><w:jc w:val="center"/></w:pPr>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:sz w:val="17"/><w:color w:val="64748B"/></w:rPr><w:t>Tel: ' . $phone . '  •  Website: ' . $website . '  •  Email: ' . $email . '</w:t></w:r>';
        $bodyXml .= '</w:p>';

        // Full document envelope with A4 page margins (1440 twips = 1 inch, top 1440, bottom 1440, left 1440, right 1440)
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" ' .
            'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<w:body>' .
            $bodyXml .
            '<w:sectPr>' .
            '<w:pgSz w:w="11906" w:h="16838"/>' . // A4 standard size
            '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>' .
            '</w:sectPr>' .
            '</w:body>' .
            '</w:document>';
    }

    private static function getContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' .
            '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>' .
            '<Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>' .
            '<Override PartName="/word/fontTable.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.fontTable+xml"/>' .
            '</Types>';
    }

    private static function getRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' .
            '</Relationships>';
    }

    private static function getDocumentRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/>' .
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/fontTable" Target="fontTable.xml"/>' .
            '</Relationships>';
    }

    private static function getStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' .
            '<w:docDefaults>' .
            '<w:rPrDefault>' .
            '<w:rPr>' .
            '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>' .
            '<w:sz w:val="22"/>' .
            '<w:szCs w:val="22"/>' .
            '<w:lang w:val="en-US"/>' .
            '</w:rPr>' .
            '</w:rPrDefault>' .
            '<w:pPrDefault>' .
            '<w:pPr>' .
            '<w:spacing w:after="160" w:line="240" w:lineRule="auto"/>' .
            '</w:pPr>' .
            '</w:pPrDefault>' .
            '</w:docDefaults>' .
            '<w:style w:type="paragraph" w:default="1" w:styleId="Normal">' .
            '<w:name w:val="Normal"/>' .
            '<w:qFormat/>' .
            '</w:style>' .
            '</w:styles>';
    }

    private static function getFontTableXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<w:fontTable xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' .
            '<w:font w:name="Calibri"><w:family w:val="swiss"/><w:pitch w:val="variable"/></w:font>' .
            '<w:font w:name="Times New Roman"><w:family w:val="roman"/><w:pitch w:val="variable"/></w:font>' .
            '<w:font w:name="Arial"><w:family w:val="swiss"/><w:pitch w:val="variable"/></w:font>' .
            '</w:fontTable>';
    }

    private static function getSettingsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' .
            '<w:defaultTabStop w:val="720"/>' .
            '<w:compat/>' .
            '</w:settings>';
    }
}
