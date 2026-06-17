<!-- ===== FILTER FORM - PREMIUM DESIGN ===== -->
<form id="attendanceFilter" class="filter-form">
    <!-- Month Select -->
    <div class="filter-group">
        <label class="filter-label">
            <i class="fas fa-calendar-alt"></i> Month
        </label>
        <div class="filter-field">
            <select name="month" id="month" class="filter-select">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromDate(null, $m)->format('F') }}
                    </option>
                @endforeach
            </select>
            <i class="fas fa-chevron-down filter-icon"></i>
        </div>
    </div>

    <!-- Year Select -->
    <div class="filter-group">
        <label class="filter-label">
            <i class="fas fa-calendar"></i> Year
        </label>
        <div class="filter-field">
            <select name="year" id="year" class="filter-select">
                @foreach(range(date('Y') - 2, date('Y') + 2) as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <i class="fas fa-chevron-down filter-icon"></i>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="filter-group filter-actions">
        <label class="filter-label">&nbsp;</label>
        <button type="submit" class="filter-btn">
            <i class="fas fa-filter"></i> Filter
        </button>
    </div>
</form>

<style>
    /* ===== FILTER FORM - PREMIUM STYLES ===== */
    .filter-form {
        display: flex;
        align-items: flex-end;
        gap: 1.25rem;
        flex-wrap: wrap;
        padding: 1.25rem 1.75rem;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02),
                    0px 8px 40px rgba(0, 0, 0, 0.04),
                    0px 20px 60px rgba(30, 58, 138, 0.06);
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        max-width: 100%;
    }

    .filter-form:hover {
        box-shadow: 0px 20px 50px rgba(0, 0, 0, 0.08),
                    0px 30px 80px rgba(30, 58, 138, 0.12);
        border-color: rgba(14, 165, 164, 0.2);
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        flex: 0 1 auto;
        min-width: 140px;
    }

    .filter-group.filter-actions {
        flex: 0 0 auto;
        min-width: auto;
    }

    .filter-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .filter-label i {
        color: var(--primary-teal, #0ea5a4);
        font-size: 0.75rem;
    }

    .filter-field {
        position: relative;
        min-width: 140px;
    }

    .filter-select {
        width: 100%;
        padding: 0.65rem 1rem 0.65rem 1rem;
        padding-right: 2.5rem;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #0f172a;
        background: white;
        transition: all 0.3s ease;
        outline: none;
        min-height: 46px;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        font-family: inherit;
    }

    .filter-select:hover {
        border-color: #cbd5e1;
    }

    .filter-select:focus {
        border-color: var(--primary-teal, #0ea5a4);
        box-shadow: 0 0 0 4px rgba(14, 165, 164, 0.12);
    }

    .filter-select option {
        padding: 0.5rem;
        font-weight: 500;
    }

    .filter-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.7rem;
        pointer-events: none;
        transition: all 0.3s ease;
    }

    .filter-select:focus ~ .filter-icon {
        color: var(--primary-teal, #0ea5a4);
        transform: translateY(-50%) rotate(180deg);
    }

    .filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.75rem;
        min-height: 46px;
        background: linear-gradient(135deg, var(--primary-teal, #0ea5a4), var(--primary-green, #22c55e));
        color: white;
        border: none;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 15px rgba(14, 165, 164, 0.25);
        white-space: nowrap;
    }

    .filter-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(14, 165, 164, 0.35);
    }

    .filter-btn:active {
        transform: translateY(0px);
    }

    .filter-btn i {
        font-size: 0.85rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
            padding: 1.25rem 1.25rem;
            gap: 0.75rem;
        }

        .filter-group {
            min-width: 100%;
            flex: 1 1 auto;
        }

        .filter-group.filter-actions {
            flex: 1 1 auto;
        }

        .filter-field {
            min-width: 100%;
        }

        .filter-select {
            min-height: 42px;
            font-size: 0.85rem;
            padding: 0.5rem 1rem 0.5rem 1rem;
            padding-right: 2.3rem;
        }

        .filter-btn {
            width: 100%;
            justify-content: center;
            min-height: 42px;
            font-size: 0.85rem;
        }

        .filter-label {
            font-size: 0.65rem;
        }
    }

    @media (max-width: 480px) {
        .filter-form {
            padding: 1rem 0.75rem;
            border-radius: 16px;
        }

        .filter-select {
            font-size: 0.8rem;
            min-height: 38px;
            padding: 0.4rem 0.8rem 0.4rem 0.8rem;
            padding-right: 2rem;
            border-radius: 12px;
        }

        .filter-btn {
            font-size: 0.8rem;
            min-height: 38px;
            padding: 0.5rem 1.25rem;
        }

        .filter-icon {
            right: 0.75rem;
            font-size: 0.6rem;
        }
    }

    /* ===== DARK MODE ===== */
    html[data-pms-theme="dark"] .filter-form {
        background: rgba(16, 33, 25, 0.95);
        border-color: rgba(122, 240, 181, 0.15);
    }

    html[data-pms-theme="dark"] .filter-label {
        color: #8ba198;
    }

    html[data-pms-theme="dark"] .filter-label i {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .filter-select {
        background: #183026;
        border-color: rgba(122, 240, 181, 0.2);
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .filter-select:hover {
        border-color: rgba(122, 240, 181, 0.3);
    }

    html[data-pms-theme="dark"] .filter-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
    }

    html[data-pms-theme="dark"] .filter-select option {
        background: #183026;
        color: #ffffff;
    }

    html[data-pms-theme="dark"] .filter-icon {
        color: #64748b;
    }

    html[data-pms-theme="dark"] .filter-select:focus ~ .filter-icon {
        color: #34d399;
    }

    html[data-pms-theme="dark"] .filter-btn {
        background: linear-gradient(135deg, #0f744c, #10b981);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);
    }

    html[data-pms-theme="dark"] .filter-btn:hover {
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
    }
</style>
