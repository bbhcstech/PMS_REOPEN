@php
    $selectedSpecial = (string) ($selectedSpecial ?? '0');
    $showOverrideHint = (bool) ($showOverrideHint ?? false);
@endphp

<div class="special-holiday-section mt-3">
    <div class="special-holiday-header">
        <div class="special-holiday-title-group">
            <span class="special-holiday-icon"><i class="fas fa-star"></i></span>
            <div>
                <div class="special-holiday-label">Special Holiday:</div>
                <p class="special-holiday-sub">Choose whether to mark this holiday as a Special Holiday.</p>
            </div>
        </div>
        <div class="special-holiday-options" role="radiogroup" aria-label="Special Holiday">
            <label class="special-option-pill">
                <input type="radio" name="is_special" value="1" {{ $selectedSpecial === '1' ? 'checked' : '' }}>
                <span>Yes</span>
            </label>
            <label class="special-option-pill">
                <input type="radio" name="is_special" value="0" {{ $selectedSpecial !== '1' ? 'checked' : '' }}>
                <span>No</span>
            </label>
        </div>
    </div>
    @if ($showOverrideHint)
        <div class="special-override-hint d-none" id="specialHolidayOverrideHint">
            <i class="fas fa-info-circle"></i>
            <span>Override existing date is enabled: overriding an existing holiday will automatically convert it into a Special Holiday (you can change it later using Edit Holiday).</span>
        </div>
    @endif
</div>

@once
<style>
    .special-holiday-section {
        padding: 16px 18px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid rgba(47, 107, 255, 0.14);
    }

    .special-holiday-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .special-holiday-title-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .special-holiday-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #fef3c7;
        color: #d97706;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .special-holiday-label {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .special-holiday-sub {
        margin: 2px 0 0;
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
    }

    .special-holiday-options {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .special-option-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 18px;
        border-radius: 999px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #1e293b;
        font-weight: 700;
        font-size: 0.86rem;
        cursor: pointer;
        transition: all 0.2s ease;
        margin: 0;
    }

    .special-option-pill input[type="radio"] {
        accent-color: #2F6BFF;
        cursor: pointer;
    }

    .special-option-pill:has(input:checked) {
        border-color: #2F6BFF;
        background: #EEF2FF;
        color: #1E4FCC;
        box-shadow: 0 4px 12px rgba(47, 107, 255, 0.14);
    }

    .special-override-hint {
        margin-top: 12px;
        padding: 10px 14px;
        border-radius: 12px;
        background: #fef3c7;
        color: #92400e;
        font-size: 0.82rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    html[data-pms-theme="dark"] .special-holiday-section {
        background: #0F1530;
        border-color: rgba(79, 131, 255, 0.18);
    }

    html[data-pms-theme="dark"] .special-holiday-label {
        color: #EEF1FB;
    }

    html[data-pms-theme="dark"] .special-holiday-sub {
        color: #94A3B8;
    }

    html[data-pms-theme="dark"] .special-holiday-icon {
        background: rgba(245, 158, 11, 0.16);
        color: #fbbf24;
    }

    html[data-pms-theme="dark"] .special-option-pill {
        background: #141B3D;
        border-color: rgba(79, 131, 255, 0.2);
        color: #E2E8F0;
    }

    html[data-pms-theme="dark"] .special-option-pill:has(input:checked) {
        background: rgba(47, 107, 255, 0.25);
        border-color: #60A5FA;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .special-override-hint {
        background: rgba(245, 158, 11, 0.14);
        color: #fde68a;
    }
</style>
@endonce
