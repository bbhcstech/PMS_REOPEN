@php
    $phoneOptions = \App\Support\CountryPhone::formMap();
    $phoneValue = (string) old($phoneField, $phoneValue ?? '');
    $phoneSelected = old('_pms_phone_codes.' . $phoneField, '+91');
    if (str_starts_with(trim($phoneValue), '+')) {
        $phoneCodes = array_unique(array_column($phoneOptions, 'dial_code'));
        usort($phoneCodes, fn ($a, $b) => strlen($b) <=> strlen($a));
        foreach ($phoneCodes as $code) {
            if (str_starts_with(preg_replace('/[\s()\-]/', '', $phoneValue), $code)) { $phoneSelected = $code; break; }
        }
    }
@endphp
<select name="_pms_phone_codes[{{ $phoneField }}]" class="form-select pms-phone-country" aria-label="{{ str_replace('_', ' ', $phoneField) }} country code" data-phone-field="{{ $phoneField }}">
    @foreach($phoneOptions as $name => $meta)
    <option value="{{ $meta['dial_code'] }}" data-iso="{{ $meta['iso'] }}" data-country="{{ $name }}" data-min-digits="{{ $meta['min_digits'] }}" data-max-digits="{{ $meta['max_digits'] }}" @selected($phoneSelected === $meta['dial_code'])>{{ strtoupper($meta['iso']) }} ({{ $meta['dial_code'] }})</option>
    @endforeach
</select>
