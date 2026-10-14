@php
    $phoneMarkup = isset($slot) ? (string) $slot : $__env->yieldContent('content');
    $phoneFieldsNeeded = $forcePhoneFields ?? (str_contains($phoneMarkup, 'data-phone-field=') || str_contains($phoneMarkup, 'id="phone_input"') || str_contains($phoneMarkup, 'id="contactDuringLeavePhone"'));
@endphp
@if($phoneFieldsNeeded)
<style>
.pms-phone-group { display:flex; align-items:stretch; gap:8px; width:100%; min-width:0; }
.pms-phone-group > input { flex:1; min-width:0; width:0; }
.pms-phone-group > .pms-phone-country { flex:0 0 135px; width:135px; max-width:40%; padding:10px 8px; font:inherit; font-size:13px; border:1px solid var(--border-color, var(--border-strong, #cbd5e1)); border-radius:8px; background:var(--bg-surface, #fff); color:var(--text-main, #172033); }
html[data-pms-theme="dark"] .pms-phone-country, html[data-theme="dark"] .pms-phone-country, html[data-bs-theme="dark"] .pms-phone-country { background:var(--bg-surface, #151b34); color:var(--text-main, #f8fafc); }
</style>
<script type="application/json" id="pms-phone-countries">{!! json_encode(\App\Support\CountryPhone::formMap(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('admin/assets/js/pms-phone-fields.js') }}?v={{ filemtime(public_path('admin/assets/js/pms-phone-fields.js')) }}"></script>
@endif
