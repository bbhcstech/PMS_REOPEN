<?php

namespace App\Support;

class LeadContactValidation
{
    public static function prepare(array $data): array
    {
        if (isset($data['email']) && is_string($data['email'])) {
            $data['email'] = trim($data['email']);
        }

        foreach (['phone', 'mobile'] as $field) {
            if (!isset($data[$field]) || !is_string($data[$field])) {
                continue;
            }

            $number = trim($data[$field]);
            $data[$field] = $number === '' ? null : $number;
            if ($number === '' || !preg_match('/^\+?[0-9 ()\.\-]+$/D', $number)) {
                continue;
            }

            $digits = preg_replace('/[^0-9]/', '', $number);
            $code = $data[$field . '_country_code'] ?? '+91';
            if (str_starts_with($number, '+')) {
                $code = null;
                for ($length = min(4, strlen($digits)); $length >= 1; $length--) {
                    $candidate = '+' . substr($digits, 0, $length);
                    if (CountryPhone::findByDialCode($candidate)) {
                        $code = $candidate;
                        $digits = substr($digits, $length);
                        break;
                    }
                }
            }

            if (is_string($code) && CountryPhone::findByDialCode($code)) {
                $data[$field] = $code . ' ' . $digits;
            }
        }

        return $data;
    }

    public static function rules(): array
    {
        $phone = static function (string $attribute, mixed $value, \Closure $fail): void {
            if (!is_string($value) || !preg_match('/^(\+[0-9]{1,4}) ([0-9]+)$/D', $value, $matches)) {
                $fail('The ' . $attribute . ' must be a valid phone number containing digits and a valid country code.');
                return;
            }

            $meta = CountryPhone::findByDialCode($matches[1]);
            if (!$meta) {
                $fail('The ' . $attribute . ' country code is invalid.');
                return;
            }

            $min = (int) $meta['min_digits'];
            $max = (int) $meta['max_digits'];
            $length = strlen($matches[2]);
            if ($length < $min || $length > $max || strlen(substr($matches[1], 1) . $matches[2]) > 15) {
                $range = $min === $max ? "exactly {$min}" : "{$min} to {$max}";
                $fail("The {$attribute} must contain {$range} digits after the {$matches[1]} country code.");
            }
        };

        $countryCode = static function (string $attribute, mixed $value, \Closure $fail): void {
            if (!is_string($value) || !CountryPhone::findByDialCode($value)) {
                $fail('Please select a valid country code.');
            }
        };

        return [
            'email' => ['bail', 'required', 'string', 'max:255', 'email:rfc', 'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/D'],
            'phone' => ['bail', 'nullable', 'string', 'max:30', $phone],
            'mobile' => ['bail', 'nullable', 'string', 'max:30', $phone],
            'phone_country_code' => ['bail', 'nullable', 'string', $countryCode],
            'mobile_country_code' => ['bail', 'nullable', 'string', $countryCode],
        ];
    }
}
