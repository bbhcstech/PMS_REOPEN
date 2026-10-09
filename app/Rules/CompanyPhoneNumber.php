<?php

namespace App\Rules;

use App\Support\CountryPhone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CompanyPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^(\+\d{1,4})\s+([0-9]+)$/', trim($value), $matches)) {
            $fail('Phone number must contain a country code followed by digits only.');
            return;
        }
        $countries = array_filter(CountryPhone::formMap(), fn ($country) => $country['dial_code'] === $matches[1]);
        if (! $countries) {
            $fail('Select a valid phone country code.');
            return;
        }
        $length = strlen($matches[2]);
        foreach ($countries as $country) {
            $max = min($country['max_digits'], 15 - strlen(ltrim($matches[1], '+')));
            if ($length >= $country['min_digits'] && $length <= $max) return;
        }
        $country = reset($countries);
        $max = min($country['max_digits'], 15 - strlen(ltrim($matches[1], '+')));
        $range = $country['min_digits'] === $max ? (string) $max : $country['min_digits'] . ' to ' . $max;
        $fail("Phone number for {$matches[1]} must contain {$range} digits.");
    }
}
