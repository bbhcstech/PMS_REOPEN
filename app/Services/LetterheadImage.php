<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Fits letterhead header/footer images to their fixed slot size (cover: scaled and centre-cropped,
 * never stretched), so any uploaded photo prints at exactly the configured size.
 */
class LetterheadImage
{
    public const HEADER = [1240, 160];
    public const FOOTER = [1240, 140];

    /** Fitted copy of a stored image (cached per file version); SVG/PDF and unknown types are returned as-is. */
    public static function fitStored(?string $path, array $size): ?string
    {
        $absolute = self::absolute($path);
        if (! $absolute || ! self::isRaster($absolute)) {
            return $absolute;
        }
        $cacheDir = storage_path('app/letterhead-cache');
        if (! is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
        $target = $cacheDir . '/' . md5($absolute . '|' . filemtime($absolute) . '|' . implode('x', $size)) . '.png';
        if (is_file($target)) {
            return $target;
        }
        return self::fit($absolute, $target, $size) ? $target : $absolute;
    }

    /** Fitted temporary copy of an uploaded image; the caller deletes it after the document is generated. */
    public static function fitUpload(UploadedFile $file, array $size): ?string
    {
        $source = $file->getRealPath();
        if (! $source || ! self::isRaster($source)) {
            return null;
        }
        $target = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lh_' . bin2hex(random_bytes(8)) . '.png';
        return self::fit($source, $target, $size) ? $target : null;
    }

    public static function absolute(?string $path): ?string
    {
        if (! $path) return null;
        if (is_file($path)) return $path;
        $public = public_path(ltrim($path, '/'));
        return is_file($public) ? $public : null;
    }

    private static function isRaster(string $path): bool
    {
        $info = @getimagesize($path);
        return is_array($info) && in_array($info[2] ?? null, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true);
    }

    private static function fit(string $source, string $target, array $size): bool
    {
        if (! function_exists('imagecreatefromstring')) return false;
        $image = @imagecreatefromstring((string) file_get_contents($source));
        if (! $image) return false;
        [$targetWidth, $targetHeight] = $size;
        $width = imagesx($image);
        $height = imagesy($image);
        // Cover: scale to fill the slot, then crop the overflow from the centre.
        $scale = max($targetWidth / $width, $targetHeight / $height);
        $cropWidth = (int) round($targetWidth / $scale);
        $cropHeight = (int) round($targetHeight / $scale);
        $sourceX = (int) max(0, floor(($width - $cropWidth) / 2));
        $sourceY = (int) max(0, floor(($height - $cropHeight) / 2));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 255, 255, 255, 127));
        imagecopyresampled($canvas, $image, 0, 0, $sourceX, $sourceY, $targetWidth, $targetHeight, $cropWidth, $cropHeight);
        $saved = imagepng($canvas, $target, 6);
        imagedestroy($canvas);
        imagedestroy($image);
        return (bool) $saved;
    }
}
