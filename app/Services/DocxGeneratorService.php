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
            mkdir($tempDir, 0755, true);
        }

        $filename = 'Letter_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6) . '.docx';
        $fullPath = $outputPath ?: ($tempDir . '/' . $filename);

        $zip = new ZipArchive();
        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create temporary DOCX file at ' . $fullPath);
        }

        $parts = self::buildParts($data);
        // Add standard OpenXML package structures, including repeating page furniture.
        $zip->addFromString('[Content_Types].xml', self::getContentTypesXml());
        $zip->addFromString('_rels/.rels', self::getRelsXml());
        $zip->addFromString('word/_rels/document.xml.rels', self::getDocumentRelsXml());
        $zip->addFromString('word/styles.xml', self::getStylesXml());
        $zip->addFromString('word/fontTable.xml', self::getFontTableXml());
        $zip->addFromString('word/settings.xml', self::getSettingsXml());
        foreach ($parts as $name => $contents) {
            $zip->addFromString($name, $contents);
        }

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
        $string = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $string ?? '') ?? '';
        return htmlspecialchars($string, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
        $signatoryName = self::xmlEscape($data['signatory_name'] ?? 'Authorized Signatory');
        $signatoryTitle = self::xmlEscape($data['signatory_title'] ?? 'Authorized Signatory');

        $rawBody = $data['body'] ?? '';
        $paragraphs = $data['body_paragraphs'] ?? preg_split('/\r\n|\r|\n/', $rawBody);

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
        $bodyXml .= '<w:pPr><w:tabs><w:tab w:val="right" w:pos="9026"/></w:tabs><w:spacing w:after="240"/></w:pPr>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:sz w:val="20"/><w:color w:val="334155"/></w:rPr><w:t>Ref No: ' . $refNo . '</w:t></w:r>';
        $bodyXml .= '<w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:sz w:val="20"/><w:color w:val="334155"/></w:rPr><w:tab/><w:t>Date: ' . $date . '</w:t></w:r>';
        $bodyXml .= '</w:p>';

        // 3. RECIPIENT BLOCK (If present)
        if ($recipientName) {
            $bodyXml .= '<w:p><w:pPr><w:spacing w:after="80"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:sz w:val="22"/><w:color w:val="0F172A"/></w:rPr><w:t>To,</w:t></w:r></w:p>';
            $bodyXml .= '<w:p><w:pPr><w:spacing w:after="200"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Calibri"/><w:b/><w:sz w:val="22"/><w:color w:val="0F172A"/></w:rPr><w:t>' . $recipientName . '</w:t></w:r></w:p>';
            foreach (['recipient_email', 'recipient_org', 'recipient_address'] as $field) {
                if (! empty($data[$field])) {
                    $bodyXml .= '<w:p><w:pPr><w:spacing w:after="80"/></w:pPr><w:r><w:rPr><w:sz w:val="20"/><w:color w:val="475569"/></w:rPr><w:t>'.self::xmlEscape($data[$field]).'</w:t></w:r></w:p>';
                }
            }
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
            $bodyXml .= '<w:widowControl/>';
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

    /** Build genuine OpenXML headers/footers, with optional embedded raster artwork. */
    private static function buildParts(array $data): array
    {
        $document = new \DOMDocument;
        $document->loadXML(self::getDocumentXml($data));
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $paragraphs = iterator_to_array($xpath->query('/w:document/w:body/w:p'));
        $header = $document->saveXML(array_shift($paragraphs));
        $footer = $document->saveXML($paragraphs[count($paragraphs) - 2]).$document->saveXML($paragraphs[count($paragraphs) - 1]);
        $nodes = $xpath->query('/w:document/w:body/w:p');
        foreach ([$nodes->item(0), $nodes->item($nodes->length - 2), $nodes->item($nodes->length - 1)] as $node) {
            $node->parentNode->removeChild($node);
        }

        $parts = [];
        foreach (['header' => LetterheadImage::HEADER, 'footer' => LetterheadImage::FOOTER] as $slot => $size) {
            $asset = $data[$slot.'_image'] ?? null;
            $path = $asset instanceof \Illuminate\Http\UploadedFile ? $asset->getRealPath() : LetterheadImage::absolute($asset);
            $image = $path && is_file($path) ? @imagecreatefromstring((string) file_get_contents($path)) : false;
            if (! $image) {
                continue; // PDF/SVG artwork keeps the textual company branding in Word.
            }
            ob_start();
            imagesavealpha($image, true);
            imagepng($image);
            $parts['word/media/'.$slot.'.png'] = ob_get_clean();
            imagedestroy($image);
            $parts['word/_rels/'.$slot.'1.xml.rels'] = '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdImage" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/'.$slot.'.png"/></Relationships>';
            $drawing = self::imageParagraph($slot, (int) round(7560310 * $size[1] / $size[0]));
            if ($slot === 'header') {
                $header = $drawing;
            } else {
                $footer = $drawing;
            }
        }

        $footer .= '<w:p><w:pPr><w:jc w:val="right"/><w:spacing w:before="60" w:after="0"/></w:pPr><w:r><w:rPr><w:sz w:val="16"/><w:color w:val="64748B"/></w:rPr><w:t xml:space="preserve">Page </w:t></w:r><w:fldSimple w:instr="PAGE"><w:r><w:t>1</w:t></w:r></w:fldSimple><w:r><w:t xml:space="preserve"> of </w:t></w:r><w:fldSimple w:instr="NUMPAGES"><w:r><w:t>1</w:t></w:r></w:fldSimple></w:p>';
        $namespace = ' xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"';
        $parts['word/header1.xml'] = '<?xml version="1.0" encoding="UTF-8"?><w:hdr'.$namespace.'>'.$header.'</w:hdr>';
        $parts['word/footer1.xml'] = '<?xml version="1.0" encoding="UTF-8"?><w:ftr'.$namespace.'>'.$footer.'</w:ftr>';
        $section = $xpath->query('//w:sectPr')->item(0);
        foreach (['footer', 'header'] as $slot) {
            $reference = $document->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:'.$slot.'Reference');
            $reference->setAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:type', 'default');
            $reference->setAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'r:id', 'rId'.ucfirst($slot));
            $section->insertBefore($reference, $section->firstChild);
        }
        $margins = $xpath->query('//w:pgMar')->item(0);
        $margins->setAttribute('w:top', isset($parts['word/media/header.png']) ? '2300' : '1900');
        $margins->setAttribute('w:bottom', isset($parts['word/media/footer.png']) ? '2300' : '1900');
        $margins->setAttribute('w:header', isset($parts['word/media/header.png']) ? '100' : '500');
        $margins->setAttribute('w:footer', isset($parts['word/media/footer.png']) ? '300' : '500');
        // Keep each short signature paragraph with the following line.
        $remaining = $xpath->query('/w:document/w:body/w:p');
        for ($i = max(0, $remaining->length - 4); $i < $remaining->length - 1; $i++) {
            $properties = $xpath->query('w:pPr', $remaining->item($i))->item(0);
            $properties->appendChild($document->createElement('w:keepNext'));
        }
        $parts['word/document.xml'] = $document->saveXML();
        return $parts;
    }

    private static function imageParagraph(string $slot, int $height): string
    {
        $id = $slot === 'header' ? 1 : 2;
        return '<w:p><w:pPr><w:ind w:left="-1440" w:right="-1440"/><w:spacing w:before="0" w:after="0"/></w:pPr><w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="7560310" cy="'.$height.'"/><wp:docPr id="'.$id.'" name="Company '.$slot.'"/><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="'.$id.'" name="'.$slot.'.png"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="rIdImage"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="7560310" cy="'.$height.'"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>';
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
            '<Override PartName="/word/header1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/>' .
            '<Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/>' .
            '<Default Extension="png" ContentType="image/png"/>' .
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
            '<Relationship Id="rIdHeader" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/>' .
            '<Relationship Id="rIdFooter" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>' .
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
            '<w:pPr><w:widowControl/>' .
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
            '<w:defaultTabStop w:val="720"/><w:updateFields w:val="true"/>' .
            '<w:compat/>' .
            '</w:settings>';
    }
}
