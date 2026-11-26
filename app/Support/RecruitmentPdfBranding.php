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
}
