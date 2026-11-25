# PDF and Word document generation

## Runtime and dependencies

Verified locally on PHP 8.2.12 and Laravel 11.45.1 on 12 October 2026.

| Component | Installed version | Purpose |
| --- | --- | --- |
| `barryvdh/laravel-dompdf` | 3.1.2 | Laravel integration for the primary server PDF renderer |
| `dompdf/dompdf` | 3.1.6 | HTML/Blade PDFs, embedded Unicode fonts, tables and pagination |
| `setasign/fpdi` | 2.6.8 | Import existing PDF letterhead artwork |
| `setasign/fpdf` | 1.9.0 | FPDI's existing PDF backend; does not lay out letter text |
| PDFMake | 0.2.23 | Existing browser table exports, with bundled Roboto fonts |
| PHP DOM, GD, mbstring, ZIP | Available | SVG/XML handling, images, Unicode and DOCX packaging |

Dompdf was upgraded from 3.1.0 to 3.1.6 and its Laravel integration from 3.1.1 to 3.1.2. FPDF/FPDI already met the requirements; their wildcard constraints are now bounded to compatible major versions. Required PHP extensions are declared in Composer. The server-specific Composer cache path was removed.

Spatie Laravel PDF and TCPDF were not added. Existing document views use tables and print CSS; the browser/Chromium infrastructure of a second server renderer is unnecessary for these documents. There are no implemented form, signing or barcode workflows requiring TCPDF. The existing OpenXML DOCX generator now supports repeating headers, footers, images and page fields without another Word-generation dependency.

The stable PDFMake 0.2 branch preserves compatibility with the existing DataTables Buttons 2.4 API. The old PDFMake beta CDN scripts, separate employee jsPDF scripts and screenshot-based super-admin html2pdf paths were replaced with the same locally bundled table renderer. Table exports retain their existing data selection, filtering, columns and filenames. Company logos are embedded when readable in the browser, preserving aspect ratio; cross-origin logos retain their company text if canvas access is denied.

## Configuration and implementation

`config/dompdf.php` configures A4, 96 DPI, DejaVu Sans, font subsetting and print media. PHP/JavaScript execution and remote downloads are disabled. Assets use local paths or embedded data URIs. Local access is limited to public assets, application storage, resources and the bundled fonts.

`App\Services\PdfDocument::loadView()` creates a fresh configured renderer and the writable `storage/app/pdf/fonts` and `storage/app/pdf/temp` directories. It adds accurate page counts through trusted canvas callbacks. Use this service for new PDF exports:

```php
use App\Services\PdfDocument;

return PdfDocument::loadView('reports.example', $data)
    ->setPaper('a4', 'landscape')
    ->download('report.pdf');
```

Include `documents.pdf-styles` before a document's own styles. It supplies page margins, repeating table headings, text wrapping and safe row breaks. Document-specific margins still take precedence. Very wide monthly attendance matrices use landscape A3; smaller reports use A4. Payslips retain their existing branded A4 design and live/snapshot payroll values.

`PdfLetterheadService` uses Dompdf for all text, including rupee symbols and accented names. FPDI places uploaded full-page PDF artwork, or separate PDF header/footer bands, behind that content on **every** page. Header/footer PDF bands preserve aspect ratio and clip excess artwork to their slots. It no longer converts text to Windows-1252 or loses artwork on continuation pages. Selected paper dimensions/orientation are respected. An invalid/unsupported PDF produces a validation message for its artwork field.

Local SVG artwork is embedded through `PdfDocument::imageSource()`. Unsupported gradients are flattened to their first explicit stop color for PDF output; the original image is unchanged. HTML-only entities are normalized to XML, and SVG text uses the bundled font. This prevents black ribbon shapes and missing footer contact lines.

`DocxGeneratorService` produces a genuine DOCX ZIP package. It uses repeating OpenXML header/footer parts, full-width raster artwork, body clearance, signature grouping, widow control and `PAGE`/`NUMPAGES` fields. The letter export controller now passes uploaded or saved header/footer images to the generator. Word updates the page fields when the document opens. Calibri is the Word document's font, with the reader's normal fallback behavior; no proprietary font is downloaded or redistributed.

Browser exports load `partials.pdf-export-scripts`. `pms-pdf-exports.js` applies consistent PDFMake typography, tables, repeated column headings and page numbers at PDF creation, including pages that reload DataTables Buttons. PDFs contain text rather than screenshots of tables.

## Deployment

```sh
composer install --no-dev --optimize-autoloader
composer check-platform-reqs --no-dev
php artisan config:clear
php artisan view:clear
php artisan package:discover
# If this deployment normally caches configuration:
php artisan config:cache
```

Deploy the committed local PDFMake assets with the rest of `public/`. The web/queue worker account needs write access to Laravel storage and bootstrap cache, especially `storage/app/pdf`, `storage/app/temp_docx` and existing letterhead storage. Restart long-running workers after deployment. No new database migration, live data changes, Node production service or browser binary is required.

## Verification

```sh
php tests/regression/document_exports.php
node tests/regression/pdf_table_exports.cjs
php tests/regression/payroll_self_service.php
```

The first test uses isolated in-memory connections and representative fixtures. It renders leave, complaints, detailed attendance, monthly attendance, simple attendance, recruitment, payslips and corporate letters. It parses the actual generated PDFs, checks pagination, repeated leave headings, Unicode amounts and page numbers, verifies full-page and band PDF artwork on every page, and checks invalid artwork errors. DOCX ZIP/XML, both embedded images, repeating part references and page fields are validated. The browser test exercises the shipped PDFMake/font bundles with a 220-row Unicode table. The payroll test checks existing ownership, HR self-restrictions, calculations and PDF downloads.

Generated review artifacts stay in ignored `storage/app/pdf/regression`. Temporary PDF.js/headless Chrome tooling was used only for local visual review, not added as an application dependency. The sampled rendered pages had no text outside their PDF page boundaries. Word package structure was validated; an Office/LibreOffice visual rendering was not available in this environment.

## Format limits and audit

- Free FPDI does not import encrypted PDFs or PDFs requiring unsupported compressed object streams. Re-save the artwork as a compatible unencrypted PDF, or upload a PNG/JPEG. The first artwork page is treated as a repeating letterhead template.
- DOCX embeds JPEG/PNG/GIF/WebP artwork through GD. PDF/SVG/full-page backgrounds retain text company branding in Word; use PNG/JPEG header/footer artwork when identical branding in both formats is required.
- Dompdf uses print CSS, not browser flex/grid layout. Fonts must contain the required glyphs. DejaVu covers the existing Latin/currency content; scripts requiring complex shaping or additional glyph coverage need a dedicated font/browser-rendering decision. A single table cell taller than an entire printable page must be redesigned into paragraphs or smaller rows.
- The final Composer audit reports **no advisories for Dompdf, its Laravel integration, FPDF or FPDI**. It still reports 53 advisories across 14 existing unrelated packages (Laravel, Guzzle, CommonMark, Flysystem, Excel/PhpSpreadsheet, PsySH and Symfony components). Those versions were not upgraded in this document-generation change; the project needs a separate compatibility-tested security update.

Primary references: [Dompdf](https://github.com/dompdf/dompdf), [Laravel Dompdf](https://github.com/barryvdh/laravel-dompdf), [FPDI importing examples](https://manuals.setasign.com/fpdi-manual/v2/the-fpdi-class/examples/), [PDFMake 0.2.23](https://github.com/bpampuch/pdfmake/releases/tag/0.2.23), [DataTables PDF export](https://datatables.net/reference/button/pdfHtml5).
