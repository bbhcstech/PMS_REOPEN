<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class GovernmentIdDobVerifier
{
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PENDING = 'pending_admin_verification';

    public function verify(string $imagePath, string $submittedDob): array
    {
        $submittedDate = $this->normalizeDate($submittedDob);

        if (! $submittedDate) {
            return $this->pending('Submitted DOB is invalid.');
        }

        $ocr = $this->readTextFromImage($imagePath);
        $ocrText = trim($ocr['text'] ?? '');

        if ($ocrText === '') {
            return $this->pending(
                'OCR could not read text from the government ID.',
                $ocrText,
                null,
                $ocr['errors'] ?? []
            );
        }

        $detected = $this->detectDobValues($ocrText);
        $detectedDob = $detected['exact'][0] ?? ($detected['year'][0] ?? null);

        if (in_array($submittedDate, $detected['exact'], true)) {
            return [
                'status' => self::STATUS_APPROVED,
                'approved' => true,
                'message' => 'DOB matched automatically.',
                'ocr_text' => $ocrText,
                'detected_dob' => $submittedDate,
                'errors' => $ocr['errors'] ?? [],
            ];
        }

        if ($detectedDob === null) {
            return $this->pending(
                'OCR text was found, but no DOB could be detected.',
                $ocrText,
                null,
                $ocr['errors'] ?? []
            );
        }

        return $this->pending(
            'OCR detected a DOB value, but it did not clearly match the submitted DOB.',
            $ocrText,
            $detectedDob,
            $ocr['errors'] ?? []
        );
    }

    public function detectDobValues(string $text): array
    {
        $text = $this->cleanOcrText($text);
        $exactDates = [];
        $years = [];

        $monthNames = 'Jan|January|Feb|February|Mar|March|Apr|April|May|Jun|June|Jul|July|Aug|August|Sep|Sept|September|Oct|October|Nov|November|Dec|December';
        $label = '(?:D\.?\s*O\.?\s*B\.?|DOB|Date\s+of\s+Birth|Birth)';

        $patterns = [
            '/\b' . $label . '\s*[:\-]?\s*(\d{1,2}\s*[\/.\-]\s*\d{1,2}\s*[\/.\-]\s*\d{4})\b/i',
            '/\b' . $label . '\s*[:\-]?\s*(\d{1,2}\s+(?:' . $monthNames . ')\s+\d{4})\b/i',
            '/\b' . $label . '\s*[:\-]?\s*((?:' . $monthNames . ')\s+\d{1,2},?\s+\d{4})\b/i',
            '/\b(\d{1,2}\s*[\/.\-]\s*\d{1,2}\s*[\/.\-]\s*\d{4})\b/',
            '/\b(\d{4}\s*[\/.\-]\s*\d{1,2}\s*[\/.\-]\s*\d{1,2})\b/',
            '/\b(\d{1,2}\s+(?:' . $monthNames . ')\s+\d{4})\b/i',
            '/\b((?:' . $monthNames . ')\s+\d{1,2},?\s+\d{4})\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match_all($pattern, $text, $matches)) {
                continue;
            }

            foreach ($matches[1] as $rawDate) {
                $date = $this->normalizeDetectedDate($rawDate);
                if ($date) {
                    $exactDates[] = $date;
                }
            }
        }

        if (preg_match_all('/\b' . $label . '\s*[:\-]?\s*((?:19|20)\d{2})\b/i', $text, $matches)) {
            foreach ($matches[1] as $year) {
                $years[] = $year;
            }
        }

        return [
            'exact' => array_values(array_unique($exactDates)),
            'year' => array_values(array_unique($years)),
        ];
    }

    private function readTextFromImage(string $imagePath): array
    {
        $errors = [];

        if (! is_file($imagePath) || ! is_readable($imagePath)) {
            $errors[] = 'Government ID image is missing or unreadable.';
            Log::warning('Government ID OCR skipped: image unreadable', ['image_path' => $imagePath]);
            return ['text' => null, 'errors' => $errors];
        }

        if ($this->isFunctionDisabled('proc_open')) {
            $errors[] = 'PHP proc_open is disabled. Tesseract cannot be executed.';
            Log::warning('Government ID OCR skipped: proc_open disabled');
            return ['text' => null, 'errors' => $errors];
        }

        $binary = (string) config('services.ocr.tesseract_binary', 'tesseract');
        if ($this->looksLikePath($binary) && (! is_file($binary) || ! is_executable($binary))) {
            $errors[] = "Tesseract binary is missing or not executable: {$binary}";
            Log::warning('Government ID OCR skipped: tesseract binary missing', ['binary' => $binary]);
            return ['text' => null, 'errors' => $errors];
        }

        $variants = $this->createOcrImageVariants($imagePath);
        $generatedVariants = array_filter($variants, fn ($path) => $path !== $imagePath);
        $results = [];

        try {
            foreach ($variants as $variant) {
                foreach ([6, 11] as $pageSegmentationMode) {
                    $process = new Process([
                        $binary,
                        $variant,
                        'stdout',
                        '-l',
                        'eng',
                        '--oem',
                        '1',
                        '--psm',
                        (string) $pageSegmentationMode,
                    ]);
                    $process->setTimeout(30);
                    $process->run();

                    if ($process->isSuccessful() && trim($process->getOutput()) !== '') {
                        $results[] = $process->getOutput();
                        continue;
                    }

                    $errors[] = trim($process->getErrorOutput() ?: $process->getOutput() ?: 'Tesseract returned no text.');
                }
            }
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
            Log::warning('Government ID OCR process failed', [
                'binary' => $binary,
                'image_path' => $imagePath,
                'exception' => $e->getMessage(),
            ]);
        } finally {
            foreach ($generatedVariants as $variant) {
                @unlink($variant);
            }
        }

        if (empty($results)) {
            Log::warning('Government ID OCR failed', [
                'binary' => $binary,
                'image_path' => $imagePath,
                'errors' => array_values(array_filter($errors)),
            ]);

            return ['text' => null, 'errors' => array_values(array_filter($errors))];
        }

        return [
            'text' => implode("\n", array_unique($results)),
            'errors' => array_values(array_filter($errors)),
        ];
    }

    private function createOcrImageVariants(string $imagePath): array
    {
        if (! function_exists('imagecreatefromstring')) {
            return [$imagePath];
        }

        $imageInfo = @getimagesize($imagePath);
        if (! $imageInfo || ($imageInfo[0] * $imageInfo[1]) > 40000000) {
            return [$imagePath];
        }

        $source = @imagecreatefromstring(file_get_contents($imagePath));
        if (! $source) {
            return [$imagePath];
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = max(1, min(3, 2400 / max($width, $height)));
        $scanWidth = (int) round($width * $scale);
        $scanHeight = (int) round($height * $scale);
        $scan = imagecreatetruecolor($scanWidth, $scanHeight);
        imagecopyresampled($scan, $source, 0, 0, 0, 0, $scanWidth, $scanHeight, $width, $height);
        imagedestroy($source);

        imagefilter($scan, IMG_FILTER_GRAYSCALE);
        imagefilter($scan, IMG_FILTER_CONTRAST, -35);
        imageconvolution($scan, [
            [-1, -1, -1],
            [-1, 9, -1],
            [-1, -1, -1],
        ], 1, 0);

        $enhancedPath = tempnam(sys_get_temp_dir(), 'government-id-ocr-');
        if ($enhancedPath === false || ! imagepng($scan, $enhancedPath)) {
            imagedestroy($scan);
            return [$imagePath];
        }

        $threshold = imagecreatetruecolor($scanWidth, $scanHeight);
        imagecopy($threshold, $scan, 0, 0, 0, 0, $scanWidth, $scanHeight);
        imagefilter($threshold, IMG_FILTER_CONTRAST, -75);
        imagefilter($threshold, IMG_FILTER_BRIGHTNESS, 10);

        $thresholdPath = tempnam(sys_get_temp_dir(), 'government-id-ocr-');
        if ($thresholdPath === false || ! imagepng($threshold, $thresholdPath)) {
            $thresholdPath = null;
        }

        imagedestroy($threshold);
        imagedestroy($scan);

        return array_values(array_filter([$imagePath, $enhancedPath, $thresholdPath]));
    }

    private function cleanOcrText(string $text): string
    {
        $text = preg_replace('/(?<=\d)[Oo](?=\d)|(?<=\d)[Oo](?=[\/.\-])|(?<=[\/.\-])[Oo](?=\d)/', '0', $text);
        $text = preg_replace('/(?<=\d)[Il](?=\d)|(?<=\d)[Il](?=[\/.\-])|(?<=[\/.\-])[Il](?=\d)/', '1', $text);

        return preg_replace('/[ \t]+/', ' ', $text);
    }

    private function normalizeDetectedDate(string $raw): ?string
    {
        $raw = preg_replace('/\s*([\/.\-])\s*/', '$1', trim($raw));
        $formats = [
            'd/m/Y', 'd-m-Y', 'd.m.Y',
            'm/d/Y', 'm-d-Y', 'm.d.Y',
            'Y/m/d', 'Y-m-d', 'Y.m.d',
            'd M Y', 'd F Y',
            'M d Y', 'F d Y',
            'M d, Y', 'F d, Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $raw);
                if ($date) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $this->normalizeDate($raw);
    }

    private function normalizeDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function pending(string $message, ?string $ocrText = null, ?string $detectedDob = null, array $errors = []): array
    {
        return [
            'status' => self::STATUS_PENDING,
            'approved' => false,
            'message' => $message,
            'ocr_text' => $ocrText,
            'detected_dob' => $detectedDob,
            'errors' => $errors,
        ];
    }

    private function isFunctionDisabled(string $function): bool
    {
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        return in_array($function, $disabled, true);
    }

    private function looksLikePath(string $binary): bool
    {
        return str_contains($binary, '/') || str_contains($binary, '\\');
    }
}
