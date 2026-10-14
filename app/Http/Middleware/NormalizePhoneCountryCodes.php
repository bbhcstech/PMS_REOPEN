<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Support\CountryPhone;
class NormalizePhoneCountryCodes
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('_pms_phone_codes')) {
            $codes = $request->input('_pms_phone_codes');
            validator(['codes' => $codes], ['codes' => 'array|max:10'])->validate();
            $allowed = array_column(CountryPhone::formMap(), 'dial_code');
            foreach ($codes as $field => $code) {
                abort_unless(in_array($field, ['phone', 'mobile', 'alternate_phone', 'whatsapp', 'office_phone', 'contact_phone', 'contact_during_leave'], true), 422);
                validator([$field => $code], [$field => ['required', 'string', \Illuminate\Validation\Rule::in($allowed)]])->validate();
                if (!$request->has($field) || !$request->filled($field)) continue;
                $raw = $request->input($field);
                validator([$field => $raw], [$field => 'string|max:40|regex:/^\+?[0-9\s().\-]+$/'])->validate();
                $digits = preg_replace('/[^0-9]/', '', $raw);
                if (str_starts_with(trim($raw), '+')) {
                    $existing = $allowed; usort($existing, fn ($a, $b) => strlen($b) <=> strlen($a));
                    $matched = false;
                    foreach ($existing as $prefix) {
                        if (str_starts_with($digits, substr($prefix, 1))) { $digits = substr($digits, strlen($prefix) - 1); $matched = true; break; }
                    }
                    if (!$matched) throw \Illuminate\Validation\ValidationException::withMessages([$field => 'Select a valid country code and enter the national phone number.']);
                }
                $request->merge([$field => $digits === '' ? null : $code . ' ' . $digits]);
            }
            $request->request->remove('_pms_phone_codes');
        }
        return $next($request);
    }
}
