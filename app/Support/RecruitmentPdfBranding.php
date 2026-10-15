<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class RecruitmentPdfBranding
{
    // Application banner standard: 180 mm wide on A4 at 254 pixels/inch.
    public const WIDTH = 1800;
    public const HEADER_HEIGHT = 240;
    public const FOOTER_HEIGHT = 160;
    public const MAX_KB = 2048;

    public static function rules(int $height): array
    {
        return ['bail', 'required', 'file', 'image', 'mimes:jpg,jpeg,png',
            'extensions:jpg,jpeg,png', 'max:'.self::MAX_KB,
            Rule::dimensions()->width(self::WIDTH)->height($height)];
    }

    public static function messages(): array
    {
        $messages = [];
        foreach (['header' => self::HEADER_HEIGHT, 'footer' => self::FOOTER_HEIGHT] as $kind => $height) {
            $key = 'pdf_'.$kind.'_image';
            $label = 'Company '.$kind;
            $messages[$key.'.required'] = 'Please upload the company '.$kind.' image for the requirement PDF.';
            foreach (['image', 'mimes', 'extensions'] as $rule) {
                $messages[$key.'.'.$rule] = $label.' must be a valid JPG, JPEG or PNG image. PDF files are not accepted.';
            }
            $messages[$key.'.max'] = $label.' must be 2 MB or smaller.';
            $messages[$key.'.dimensions'] = $label.' must be exactly '.self::WIDTH.' × '.$height.' pixels. Images are not cropped or stretched.';
        }
        return $messages;
    }
}
