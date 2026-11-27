<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\LetterheadSignature;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LetterheadSignatureService
{
    public const DEFAULT_WIDTH_MM = 40;
    public const MIN_WIDTH_MM = 15;
    public const MAX_WIDTH_MM = 90;

    public static function maxUploadKb(): int
    {
        return (int) config('letterhead.signature_max_kb', 2048);
    }

    /**
     * Validate and store a drawn signature data URL onto private local storage.
     *
     * @return array{path: string, mime_type: string, width_px: int, height_px: int}
     */
    public static function storeFromDataUrl(string $dataUrl, ?int $companyId): array
    {
        $binary = self::decodeAndValidatePngDataUrl($dataUrl);
        return self::writePrivatePng($binary, 'letterhead-signatures/' . ($companyId ?: 'global'));
    }

    /**
     * Validate and store an uploaded signature image (PNG/JPG/JPEG) onto private local storage.
     *
     * @return array{path: string, mime_type: string, width_px: int, height_px: int}
     */
    public static function storeFromUploadedFile(UploadedFile $file, ?int $companyId): array
    {
        $binary = self::validateAndNormalizeUpload($file);
        return self::writePrivatePng($binary, 'letterhead-signatures/' . ($companyId ?: 'global'));
    }

    /**
     * Validate a drawn signature data URL and write to a temporary PNG file for one-time export.
     */
    public static function createTemporaryFromDataUrl(string $dataUrl): string
    {
        $binary = self::decodeAndValidatePngDataUrl($dataUrl);
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lh_sig_' . bin2hex(random_bytes(8)) . '.png';
        file_put_contents($tmp, $binary);
        return $tmp;
    }

    /**
     * Validate an uploaded signature file and write to a temporary PNG file for one-time export.
     */
    public static function createTemporaryFromUpload(UploadedFile $file): string
    {
        $binary = self::validateAndNormalizeUpload($file);
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lh_sig_' . bin2hex(random_bytes(8)) . '.png';
        file_put_contents($tmp, $binary);
        return $tmp;
    }

    /**
     * Copy a signature file into an immutable snapshot path on private storage for finalized letters.
     */
    public static function createSnapshotFromAbsolute(string $absolutePath, ?int $companyId): ?string
    {
        if (! is_file($absolutePath)) {
            return null;
        }
        $bytes = @file_get_contents($absolutePath);
        if ($bytes === false || $bytes === '') {
            return null;
        }
        $dir = 'letterhead-signature-snapshots/' . ($companyId ?: 'global');
        $path = $dir . '/' . Str::uuid()->toString() . '.png';
        Storage::disk('local')->put($path, $bytes);
        return $path;
    }

    public static function absolutePath(?string $storagePath): ?string
    {
        if (! $storagePath) {
            return null;
        }
        if (Storage::disk('local')->exists($storagePath)) {
            return Storage::disk('local')->path($storagePath);
        }
        return null;
    }

    public static function deletePrivateFile(?string $storagePath): void
    {
        if ($storagePath && Storage::disk('local')->exists($storagePath)) {
            Storage::disk('local')->delete($storagePath);
        }
    }

    public static function authorizeSignature(User $actor, LetterheadSignature $signature, bool $forManagement = false): void
    {
        if ($signature->company_id) {
            TenantScope::authorizeCompany((int) $signature->company_id);
        }

        $role = method_exists($actor, 'normalizedRole')
            ? $actor->normalizedRole()
            : strtolower((string) $actor->role);

        $isOrgAdmin = in_array($role, ['admin', 'superadmin', 'administrator', 'hr'], true);

        if ((int) $signature->user_id === (int) $actor->id) {
            return;
        }

        if ($isOrgAdmin && (! $signature->company_id || (int) $signature->company_id === (int) ($actor->company_id ?: TenantScope::companyId()) || $role === 'superadmin')) {
            return;
        }

        abort(403, $forManagement
            ? 'You are not authorized to manage this signature.'
            : 'You are not authorized to apply this signature.');
    }

    public static function recordAudit(string $action, array $details, ?int $entityId = null): void
    {
        try {
            $user = auth()->user();
            $companyId = $details['company_id'] ?? TenantScope::companyId() ?? $user?->company_id;
            AuditLog::create([
                'user_id' => $user?->id,
                'company_id' => $companyId,
                'action' => $action,
                'entity_type' => 'letterhead_signature',
                'entity_id' => $entityId,
                'new_values' => $details,
                'ip_address' => request()?->ip(),
                'user_agent' => substr((string) request()?->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            // Do not fail document generation if central audit DB is unavailable in isolated test contexts.
        }
    }

    private static function decodeAndValidatePngDataUrl(string $dataUrl): string
    {
        if (! preg_match('/^data:image\/png;base64,([A-Za-z0-9+\/=]+)$/', trim($dataUrl), $matches)) {
            throw ValidationException::withMessages([
                'signature_data' => 'Drawn signature must be a valid PNG image.',
            ]);
        }

        $raw = base64_decode($matches[1], true);
        if ($raw === false || strlen($raw) === 0) {
            throw ValidationException::withMessages([
                'signature_data' => 'Unable to decode the drawn signature image.',
            ]);
        }

        $maxBytes = self::maxUploadKb() * 1024;
        if (strlen($raw) > $maxBytes) {
            throw ValidationException::withMessages([
                'signature_data' => 'Drawn signature exceeds the maximum allowed size of ' . self::maxUploadKb() . ' KB.',
            ]);
        }

        $info = @getimagesizefromstring($raw);
        if (! is_array($info) || ($info[2] ?? null) !== IMAGETYPE_PNG) {
            throw ValidationException::withMessages([
                'signature_data' => 'Drawn signature is not a valid PNG image.',
            ]);
        }

        $img = @imagecreatefromstring($raw);
        if (! $img) {
            throw ValidationException::withMessages([
                'signature_data' => 'Unable to process the drawn signature image.',
            ]);
        }

        if (! self::hasVisiblePixels($img)) {
            imagedestroy($img);
            throw ValidationException::withMessages([
                'signature_data' => 'Please draw a signature before saving or applying.',
            ]);
        }

        $png = self::reencodePng($img);
        imagedestroy($img);
        return $png;
    }

    private static function validateAndNormalizeUpload(UploadedFile $file): string
    {
        $maxKb = self::maxUploadKb();
        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages([
                'signature_file' => 'Signature image must be ' . round($maxKb / 1024, 1) . ' MB or smaller.',
            ]);
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
            throw ValidationException::withMessages([
                'signature_file' => 'Signature image must be a PNG, JPG, or JPEG file.',
            ]);
        }

        $raw = @file_get_contents($file->getRealPath());
        if ($raw === false || $raw === '') {
            throw ValidationException::withMessages([
                'signature_file' => 'Unable to read the uploaded signature file.',
            ]);
        }

        $info = @getimagesizefromstring($raw);
        if (! is_array($info) || ! in_array($info[2] ?? null, [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) {
            throw ValidationException::withMessages([
                'signature_file' => 'Invalid image content. Upload a genuine PNG, JPG, or JPEG signature image.',
            ]);
        }

        $img = @imagecreatefromstring($raw);
        if (! $img) {
            throw ValidationException::withMessages([
                'signature_file' => 'Unable to process the uploaded signature image.',
            ]);
        }

        $png = self::reencodePng($img);
        imagedestroy($img);
        return $png;
    }

    private static function reencodePng(\GdImage $img): string
    {
        imagealphablending($img, false);
        imagesavealpha($img, true);
        ob_start();
        imagepng($img);
        return (string) ob_get_clean();
    }

    private static function hasVisiblePixels(\GdImage $img): bool
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $stepX = max(1, (int) floor($w / 120));
        $stepY = max(1, (int) floor($h / 60));

        for ($y = 0; $y < $h; $y += $stepY) {
            for ($x = 0; $x < $w; $x += $stepX) {
                $rgba = imagecolorat($img, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;
                if ($alpha < 120) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return array{path: string, mime_type: string, width_px: int, height_px: int}
     */
    private static function writePrivatePng(string $pngBytes, string $directory): array
    {
        $info = @getimagesizefromstring($pngBytes);
        $path = $directory . '/' . Str::uuid()->toString() . '.png';
        Storage::disk('local')->put($path, $pngBytes);

        return [
            'path' => $path,
            'mime_type' => 'image/png',
            'width_px' => (int) ($info[0] ?? 0),
            'height_px' => (int) ($info[1] ?? 0),
        ];
    }
}
