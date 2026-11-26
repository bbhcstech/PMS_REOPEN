<?php

namespace App\Support;

use App\Models\RecruitmentBrandingAsset;
use Illuminate\Validation\Rule;

class RecruitmentPdfBranding
{
    public const WIDTH = 1240;
    public const HEADER_HEIGHT = 160;
    public const FOOTER_HEIGHT = 140;
    public const HEIGHTS = ['header' => self::HEADER_HEIGHT, 'footer' => self::FOOTER_HEIGHT];
    public const MAX_KB = 2048;

    public static function rules(int $height, string $presence = 'required'): array
    {
        return ['bail', $presence, 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png',
            'extensions:jpg,jpeg,png', 'max:'.self::MAX_KB,
            Rule::dimensions()->width(self::WIDTH)->height($height)];
    }

    public static function selectionRules(int $companyId): array
    {
        $rules = [];
        foreach (self::HEIGHTS as $kind => $height) {
            $image = 'pdf_'.$kind.'_image';
            $asset = 'pdf_'.$kind.'_asset_id';
            $rules[$image] = self::rules($height, 'required_without:'.$asset);
            $rules[$asset] = ['bail', 'nullable', 'integer', 'prohibits:'.$image,
                Rule::exists(RecruitmentBrandingAsset::class, 'id')->where('company_id', $companyId)->where('kind', $kind)];
            $rules['pdf_'.$kind.'_name'] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }

    public static function messages(): array
    {
        $messages = [];
        foreach (self::HEIGHTS as $kind => $height) {
            $key = 'pdf_'.$kind.'_image';
            $label = 'Company '.$kind;
            $messages[$key.'.required'] = $messages[$key.'.required_without'] = 'Choose a saved '.$kind.' or upload a new image.';
            $messages['pdf_'.$kind.'_asset_id.exists'] = 'Choose a '.$kind.' saved for this company.';
            $messages['pdf_'.$kind.'_asset_id.prohibits'] = 'Choose a saved '.$kind.' or upload a new image, one at a time.';
            $messages['pdf_'.$kind.'_name.max'] = 'Use 100 characters or fewer for the saved '.$kind.' name.';
            foreach (['image', 'mimes', 'extensions'] as $rule) {
                $messages[$key.'.'.$rule] = $label.' must be a valid JPG, JPEG or PNG image. PDF files are not accepted.';
            }
            $messages[$key.'.max'] = $label.' must be 2 MB or smaller.';
            $messages[$key.'.dimensions'] = $label.' must be exactly '.self::WIDTH.' × '.$height.' pixels. Images are not cropped or stretched.';
        }
        return $messages;
    }

    /**
     * Branding images are stored base64-encoded in one INSERT, which must fit the MySQL server's
     * max_allowed_packet (1 MB on a default XAMPP install). Keeps the original bytes when they fit;
     * otherwise re-encodes the same pixels (same width × height) as a high-quality JPEG on white.
     * Returns null when the image cannot be made to fit.
     *
     * @return array{bytes: string, mime: string}|null
     */
    public static function fitForDatabase(string $bytes, string $mime, int $packetBytes): ?array
    {
        // Leave room for the rest of the INSERT statement and protocol overhead.
        $budget = max(64 * 1024, $packetBytes - 64 * 1024);
        $fits = fn (string $data) => 4 * (int) ceil(strlen($data) / 3) <= $budget;
        if ($fits($bytes)) {
            return ['bytes' => $bytes, 'mime' => $mime];
        }
        if (! function_exists('imagecreatefromstring') || ! ($source = @imagecreatefromstring($bytes))) {
            return null;
        }
        $width = imagesx($source);
        $height = imagesy($source);
        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);
        imagedestroy($source);
        try {
            foreach ([92, 86, 80, 72, 64, 56] as $quality) {
                ob_start();
                imagejpeg($canvas, null, $quality);
                $jpeg = (string) ob_get_clean();
                if ($jpeg !== '' && $fits($jpeg)) {
                    return ['bytes' => $jpeg, 'mime' => 'image/jpeg'];
                }
            }
        } finally {
            imagedestroy($canvas);
        }

        return null;
    }
}
