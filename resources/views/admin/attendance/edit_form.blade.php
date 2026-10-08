<form method="POST" action="{{ $attendance ? route('attendance.update', $attendance->id) : route('attendance.store') }}" class="attendance-form">
    @csrf
    @if($attendance)
        @method('PUT')
    @endif

    <!-- Hidden User & Date -->
    <input type="hidden" name="user_id[]" value="{{ $userId }}">
    <input type="hidden" name="date" value="{{ $date }}">

    <!-- ===== EMPLOYEE INFO CARD ===== -->
    <div class="employee-card">
        <div class="employee-avatar-wrapper">
            <img src="{{ (!empty($employee->profile_image) && file_exists(public_path($employee->profile_image))) ? asset($employee->profile_image) : asset('images/default-avatar.png') }}"
                 alt="{{ $employee->name }}" class="employee-avatar" onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}';">
            <div class="employee-status-dot"></div>
        </div>
        <div class="employee-info">
            <h4 class="employee-name">{{ $employee->name }}</h4>
            <div class="employee-meta">
                <span class="employee-designation">
                    <i class="fas fa-user-tag"></i> {{ $employee->designation ?? 'N/A' }}
                </span>
                <span class="employee-department">
                    <i class="fas fa-building"></i> {{ $employee->department->dpt_name ?? 'N/A' }}
                </span>
                <span class="employee-date">
                    <i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                </span>
            </div>
        </div>
    </div>

    <!-- ===== FORM FIELDS ===== -->
    <div class="form-grid">

        <!-- Location -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-map-marker-alt"></i> Location <sup class="required-star">*</sup>
            </label>
            <div class="form-field">
                <select name="location_id" class="form-control">
                    <option value="">Custom location / select a saved location</option>
                    @foreach ($location as $loc)
                        <option value="{{ $loc->id }}"
                            @selected(old('location_id', $attendance->location_id) == $loc->id)>
                            {{ $loc->location }}
                        </option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down field-icon"></i>
            </div>
            <label for="attendance_location" class="form-label mt-2">Custom location / address</label>
            <input type="text" name="clock_in_address" id="attendance_location" class="form-control" maxlength="1000" value="{{ old('clock_in_address', $attendance->clock_in_address) }}" placeholder="Enter the employee's attendance location">
            @error('clock_in_address')<span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <!-- Clock In -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-sign-in-alt"></i> Clock In <sup class="required-star">*</sup>
            </label>
            <div class="form-field">
                <input type="time" name="clock_in" class="form-control" required
                    value="{{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '10:30' }}">
                <i class="fas fa-clock field-icon"></i>
            </div>
        </div>

        <!-- Clock Out -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-sign-out-alt"></i> Clock Out
            </label>
            <div class="form-field">
                <input type="time" name="clock_out" class="form-control"
                    value="{{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '19:30' }}">
                <i class="fas fa-clock field-icon"></i>
            </div>
        </div>

        <!-- Status -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-flag"></i> Status
            </label>
            <div class="form-field">
                <select name="status" class="form-control">
                    @php $status = $attendance->status ?? 'absent'; @endphp
                    <option value="present" {{ $status == 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ $status == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="late" {{ $status == 'late' ? 'selected' : '' }}>Late</option>
                    <option value="half_day" {{ $status == 'half_day' ? 'selected' : '' }}>Half Day</option>
                    <option value="day_off" {{ $status == 'day_off' ? 'selected' : '' }}>Day Off</option>
                    <option value="leave" {{ $status == 'leave' ? 'selected' : '' }}>Leave</option>
                    <option value="holiday" {{ $status == 'holiday' ? 'selected' : '' }}>Holiday</option>
                    <option value="wfh" {{ $status == 'wfh' ? 'selected' : '' }}>Work From Home (WFH)</option>
                </select>
                <i class="fas fa-chevron-down field-icon"></i>
            </div>
        </div>

        <!-- Late Radio -->
        <div class="form-group radio-group">
            <label class="form-label">
                <i class="fas fa-clock"></i> Late
            </label>
            <div class="radio-options">
                <label class="radio-option">
                    <input type="radio" name="late" value="yes" {{ ($attendance->status ?? '') == 'late' ? 'checked' : '' }}>
                    <span class="radio-label">Yes</span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="late" value="no" {{ ($attendance->status ?? '') != 'late' ? 'checked' : '' }}>
                    <span class="radio-label">No</span>
                </label>
            </div>
        </div>

        <!-- Half Day Radio -->
        <div class="form-group radio-group">
            <label class="form-label">
                <i class="fas fa-star-half-alt"></i> Half Day
            </label>
            <div class="radio-options">
                <label class="radio-option">
                    <input type="radio" name="half_day" value="yes" {{ ($attendance->status ?? '') == 'half_day' ? 'checked' : '' }}>
                    <span class="radio-label">Yes</span>
                </label>
                <label class="radio-option">
                    <input type="radio" name="half_day" value="no" {{ ($attendance->status ?? '') != 'half_day' ? 'checked' : '' }}>
                    <span class="radio-label">No</span>
                </label>
            </div>
        </div>

        <!-- Working From -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-laptop-house"></i> Working From
            </label>
            <div class="form-field">
                <select name="work_from_type" id="work_from_type" class="form-control">
                    <option value="office" {{ ($attendance->work_from_type ?? '') == 'office' ? 'selected' : '' }}>Office</option>
                    <option value="home" {{ ($attendance->work_from_type ?? '') == 'home' ? 'selected' : '' }}>Home</option>
                    <option value="other" {{ ($attendance->work_from_type ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
                <i class="fas fa-chevron-down field-icon"></i>
            </div>
        </div>

        <!-- Other Location (conditional) -->
        <div class="form-group {{ ($attendance->work_from_type ?? '') == 'other' ? '' : 'hidden' }}" id="other_location_div">
            <label class="form-label">
                <i class="fas fa-map-pin"></i> Other Location
            </label>
            <div class="form-field">
                <input type="text" name="working_from" class="form-control" value="{{ $attendance->working_from ?? '' }}" placeholder="Enter location...">
                <i class="fas fa-pen field-icon"></i>
            </div>
        </div>

        <!-- Overwrite Attendance -->
        <div class="form-group checkbox-group">
            <label class="form-label">
                <i class="fas fa-exchange-alt"></i> Overwrite
            </label>
            <label class="checkbox-option">
                <input type="checkbox" name="overwrite_attendance" value="yes" {{ ($attendance->overwrite_attendance ?? '') == 'yes' ? 'checked' : '' }}>
                <span class="checkbox-label">Attendance Overwrite</span>
            </label>
        </div>

    </div>

    <!-- ===== ACTION BUTTONS ===== -->
    <div class="form-actions">
        <button type="submit" class="btn-submit">
            <i class="fas {{ $attendance ? 'fa-save' : 'fa-plus-circle' }}"></i>
            {{ $attendance ? 'Update' : 'Save' }} Attendance
        </button>
        <button type="button" class="btn-cancel" data-attendance-modal-close>
            <i class="fas fa-times"></i> Cancel
        </button>
    </div>

</form>

<style>
    /* ===== ATTENDANCE FORM STYLES ===== */
    .attendance-form {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 2rem 2.25rem;
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.02),
                    0px 8px 40px rgba(0, 0, 0, 0.04),
                    0px 20px 60px rgba(47, 107, 255, 0.06);
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        max-width: 900px;
        margin: 0 auto;
    }

    .attendance-form:hover {
        box-shadow: 0px 20px 50px rgba(0, 0, 0, 0.08),
                    0px 30px 80px rgba(47, 107, 255, 0.12);
        border-color: rgba(47, 107, 255, 0.2);
    }

    /* ===== EMPLOYEE CARD ===== */
    .employee-card {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem 1.75rem;
        margin-bottom: 2rem;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .employee-card:hover {
        border-color: var(--primary-blue, #2F6BFF);
        box-shadow: 0 4px 15px rgba(47, 107, 255, 0.08);
    }

    .employee-avatar-wrapper {
        position: relative;
        flex-shrink: 0;
    }

    .employee-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .employee-status-dot {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 16px;
        height: 16px;
        background: #22c55e;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
    }

    .employee-info {
        flex: 1;
    }

    .employee-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 0.25rem 0;
    }

    .employee-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: 0.85rem;
        color: #64748b;
    }

    .employee-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .employee-meta i {
        color: var(--primary-blue, #2F6BFF);
        font-size: 0.75rem;
    }

    /* ===== FORM GRID ===== */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem 1.75rem;
        margin-bottom: 1.75rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .form-group.hidden {
        display: none;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-label i {
        color: var(--primary-blue, #2F6BFF);
        font-size: 0.8rem;
    }

    .required-star {
        color: #ef4444;
        font-size: 1rem;
        line-height: 1;
    }

    .form-field {
        position: relative;
    }

    .form-field .form-control {
        width: 100%;
        padding: 0.7rem 1rem 0.7rem 2.8rem;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #0f172a;
        background: white;
        transition: all 0.3s ease;
        outline: none;
        min-height: 48px;
        appearance: none;
        -webkit-appearance: none;
    }

    .form-field .form-control:focus {
        border-color: var(--primary-blue, #2F6BFF);
        box-shadow: 0 0 0 4px rgba(47, 107, 255, 0.12);
    }

    .form-field .form-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    .form-field select.form-control {
        padding-right: 2.8rem;
        cursor: pointer;
    }

    .form-field .field-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.9rem;
        pointer-events: none;
        transition: all 0.3s ease;
    }

    .form-field .form-control:focus ~ .field-icon {
        color: var(--primary-blue, #2F6BFF);
    }

    .form-field .fa-chevron-down.field-icon {
        left: auto;
        right: 1rem;
        font-size: 0.7rem;
    }

    /* ===== RADIO GROUP ===== */
    .radio-group .radio-options {
        display: flex;
        gap: 0.75rem;
        padding-top: 0.2rem;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        cursor: pointer;
        padding: 0.4rem 1rem;
        border-radius: 30px;
        background: #f1f5f9;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .radio-option:hover {
        background: #e2e8f0;
    }

    .radio-option input[type="radio"] {
        display: none;
    }

    .radio-option input[type="radio"]:checked + .radio-label {
        color: var(--primary-blue, #2F6BFF);
    }

    .radio-option:has(input:checked) {
        background: #EEF2FF;
        border-color: var(--primary-blue, #2F6BFF);
    }

    .radio-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    /* ===== CHECKBOX GROUP ===== */
    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .checkbox-option {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .checkbox-option:hover {
        background: #f1f5f9;
    }

    .checkbox-option input[type="checkbox"] {
        width: 18px;
        height: 18px;
        border-radius: 6px;
        border: 2px solid #cbd5e1;
        accent-color: var(--primary-blue, #2F6BFF);
        cursor: pointer;
        flex-shrink: 0;
    }

    .checkbox-option input[type="checkbox"]:checked {
        border-color: var(--primary-blue, #2F6BFF);
    }

    .checkbox-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
    }

    .checkbox-option:has(input:checked) {
        border-color: var(--primary-blue, #2F6BFF);
        background: #EEF2FF;
    }

    /* ===== ACTION BUTTONS ===== */
    .form-actions {
        display: flex;
        gap: 1rem;
        padding-top: 1.5rem;
        border-top: 2px solid #e2e8f0;
        flex-wrap: wrap;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.8rem 2rem;
        background: linear-gradient(135deg, #2F6BFF, #1E4FCC);
        color: white;
        border: none;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 15px rgba(47, 107, 255, 0.25);
        text-decoration: none;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(47, 107, 255, 0.35);
    }

    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.8rem 2rem;
        background: #f1f5f9;
        color: #475569;
        border: 2px solid #e2e8f0;
        border-radius: 40px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #0f172a;
        transform: translateY(-2px);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .attendance-form {
            padding: 1.5rem 1.5rem;
            margin: 0 0.5rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .employee-card {
            flex-direction: column;
            text-align: center;
            padding: 1.25rem;
        }

        .employee-meta {
            justify-content: center;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-submit,
        .btn-cancel {
            justify-content: center;
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .attendance-form {
            padding: 1rem 1rem;
            border-radius: 20px;
        }

        .employee-avatar {
            width: 56px;
            height: 56px;
        }

        .employee-name {
            font-size: 1rem;
        }

        .employee-meta {
            font-size: 0.75rem;
            gap: 0.5rem;
            flex-direction: column;
            align-items: center;
        }

        .radio-options {
            flex-wrap: wrap;
        }

        .radio-option {
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
        }

        .checkbox-option {
            padding: 0.4rem 0.8rem;
        }

        .form-label {
            font-size: 0.65rem;
        }

        .form-field .form-control {
            font-size: 0.8rem;
            padding: 0.5rem 0.8rem 0.5rem 2.4rem;
            min-height: 40px;
        }

        .form-field .field-icon {
            font-size: 0.8rem;
            left: 0.8rem;
        }
    }

    /* ===== DARK MODE ===== */
    html[data-pms-theme="dark"] .attendance-form,
    html[data-theme="dark"] .attendance-form,
    html[data-bs-theme="dark"] .attendance-form,
    html.dark .attendance-form,
    body[data-pms-theme="dark"] .attendance-form,
    body[data-theme="dark"] .attendance-form,
    body[data-bs-theme="dark"] .attendance-form,
    body.dark-mode .attendance-form,
    body.dark .attendance-form,
    [data-pms-theme="dark"] .attendance-form,
    [data-theme="dark"] .attendance-form,
    [data-bs-theme="dark"] .attendance-form,
    .dark-mode .attendance-form {
        background: #0F1530 !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6) !important;
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .attendance-form:hover,
    html[data-theme="dark"] .attendance-form:hover,
    html[data-bs-theme="dark"] .attendance-form:hover,
    html.dark .attendance-form:hover,
    body[data-pms-theme="dark"] .attendance-form:hover,
    body[data-theme="dark"] .attendance-form:hover,
    body[data-bs-theme="dark"] .attendance-form:hover,
    body.dark-mode .attendance-form:hover,
    body.dark .attendance-form:hover,
    [data-pms-theme="dark"] .attendance-form:hover,
    [data-theme="dark"] .attendance-form:hover,
    [data-bs-theme="dark"] .attendance-form:hover,
    .dark-mode .attendance-form:hover {
        border-color: rgba(79, 131, 255, 0.35) !important;
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.7) !important;
    }

    html[data-pms-theme="dark"] .employee-card,
    html[data-theme="dark"] .employee-card,
    html[data-bs-theme="dark"] .employee-card,
    html.dark .employee-card,
    body[data-pms-theme="dark"] .employee-card,
    body[data-theme="dark"] .employee-card,
    body[data-bs-theme="dark"] .employee-card,
    body.dark-mode .employee-card,
    body.dark .employee-card,
    [data-pms-theme="dark"] .employee-card,
    [data-theme="dark"] .employee-card,
    [data-bs-theme="dark"] .employee-card,
    .dark-mode .employee-card {
        background: #141B3D !important;
        border: 1px solid rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .employee-card:hover,
    html[data-theme="dark"] .employee-card:hover,
    html[data-bs-theme="dark"] .employee-card:hover,
    body.dark-mode .employee-card:hover,
    [data-pms-theme="dark"] .employee-card:hover,
    [data-theme="dark"] .employee-card:hover,
    .dark-mode .employee-card:hover {
        border-color: #3B82F6 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
    }

    html[data-pms-theme="dark"] .employee-name,
    html[data-theme="dark"] .employee-name,
    html[data-bs-theme="dark"] .employee-name,
    html.dark .employee-name,
    body[data-pms-theme="dark"] .employee-name,
    body[data-theme="dark"] .employee-name,
    body[data-bs-theme="dark"] .employee-name,
    body.dark-mode .employee-name,
    body.dark .employee-name,
    [data-pms-theme="dark"] .employee-name,
    [data-theme="dark"] .employee-name,
    [data-bs-theme="dark"] .employee-name,
    .dark-mode .employee-name {
        color: #FFFFFF !important;
    }

    html[data-pms-theme="dark"] .employee-meta,
    html[data-theme="dark"] .employee-meta,
    html[data-bs-theme="dark"] .employee-meta,
    body.dark-mode .employee-meta,
    [data-pms-theme="dark"] .employee-meta,
    [data-theme="dark"] .employee-meta,
    .dark-mode .employee-meta {
        color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .employee-meta span,
    html[data-theme="dark"] .employee-meta span,
    html[data-bs-theme="dark"] .employee-meta span,
    body.dark-mode .employee-meta span,
    [data-pms-theme="dark"] .employee-meta span,
    [data-theme="dark"] .employee-meta span,
    .dark-mode .employee-meta span {
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .employee-meta i,
    html[data-theme="dark"] .employee-meta i,
    body.dark-mode .employee-meta i,
    [data-theme="dark"] .employee-meta i {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .form-label,
    html[data-theme="dark"] .form-label,
    html[data-bs-theme="dark"] .form-label,
    html.dark .form-label,
    body[data-pms-theme="dark"] .form-label,
    body[data-theme="dark"] .form-label,
    body[data-bs-theme="dark"] .form-label,
    body.dark-mode .form-label,
    body.dark .form-label,
    [data-pms-theme="dark"] .form-label,
    [data-theme="dark"] .form-label,
    [data-bs-theme="dark"] .form-label,
    .dark-mode .form-label {
        color: #CBD5E1 !important;
        font-weight: 700 !important;
    }

    html[data-pms-theme="dark"] .form-label i,
    html[data-theme="dark"] .form-label i,
    body.dark-mode .form-label i,
    [data-theme="dark"] .form-label i {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .required-star,
    html[data-theme="dark"] .required-star,
    body.dark-mode .required-star {
        color: #F87171 !important;
    }

    html[data-pms-theme="dark"] .form-field .form-control,
    html[data-theme="dark"] .form-field .form-control,
    html[data-bs-theme="dark"] .form-field .form-control,
    html.dark .form-field .form-control,
    body[data-pms-theme="dark"] .form-field .form-control,
    body[data-theme="dark"] .form-field .form-control,
    body[data-bs-theme="dark"] .form-field .form-control,
    body.dark-mode .form-field .form-control,
    body.dark .form-field .form-control,
    [data-pms-theme="dark"] .form-field .form-control,
    [data-theme="dark"] .form-field .form-control,
    [data-bs-theme="dark"] .form-field .form-control,
    .dark-mode .form-field .form-control {
        background: #141B3D !important;
        border: 2px solid rgba(79, 131, 255, 0.25) !important;
        color: #FFFFFF !important;
        color-scheme: dark !important;
    }

    html[data-pms-theme="dark"] .form-field .form-control:focus,
    html[data-theme="dark"] .form-field .form-control:focus,
    html[data-bs-theme="dark"] .form-field .form-control:focus,
    body.dark-mode .form-field .form-control:focus,
    [data-pms-theme="dark"] .form-field .form-control:focus,
    [data-theme="dark"] .form-field .form-control:focus,
    .dark-mode .form-field .form-control:focus {
        border-color: #3B82F6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2) !important;
        background: #141B3D !important;
        color: #FFFFFF !important;
    }

    html[data-pms-theme="dark"] .form-field .form-control::placeholder,
    html[data-theme="dark"] .form-field .form-control::placeholder,
    body.dark-mode .form-field .form-control::placeholder,
    [data-theme="dark"] .form-field .form-control::placeholder {
        color: #64748B !important;
    }

    html[data-pms-theme="dark"] .form-field select.form-control option,
    html[data-theme="dark"] .form-field select.form-control option,
    html[data-bs-theme="dark"] .form-field select.form-control option,
    body.dark-mode .form-field select.form-control option,
    [data-pms-theme="dark"] .form-field select.form-control option,
    [data-theme="dark"] .form-field select.form-control option,
    .dark-mode .form-field select.form-control option {
        background: #141B3D !important;
        color: #FFFFFF !important;
    }

    html[data-pms-theme="dark"] .form-field .field-icon,
    html[data-theme="dark"] .form-field .field-icon,
    html[data-bs-theme="dark"] .form-field .field-icon,
    body.dark-mode .form-field .field-icon,
    [data-pms-theme="dark"] .form-field .field-icon,
    [data-theme="dark"] .form-field .field-icon,
    .dark-mode .form-field .field-icon {
        color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .form-field .form-control:focus ~ .field-icon,
    html[data-theme="dark"] .form-field .form-control:focus ~ .field-icon,
    body.dark-mode .form-field .form-control:focus ~ .field-icon,
    [data-theme="dark"] .form-field .form-control:focus ~ .field-icon {
        color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .form-field .fa-chevron-down.field-icon,
    html[data-theme="dark"] .form-field .fa-chevron-down.field-icon,
    body.dark-mode .form-field .fa-chevron-down.field-icon {
        color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .radio-option,
    html[data-theme="dark"] .radio-option,
    html[data-bs-theme="dark"] .radio-option,
    html.dark .radio-option,
    body[data-pms-theme="dark"] .radio-option,
    body[data-theme="dark"] .radio-option,
    body[data-bs-theme="dark"] .radio-option,
    body.dark-mode .radio-option,
    body.dark .radio-option,
    [data-pms-theme="dark"] .radio-option,
    [data-theme="dark"] .radio-option,
    [data-bs-theme="dark"] .radio-option,
    .dark-mode .radio-option {
        background: #141B3D !important;
        border: 2px solid rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .radio-option:hover,
    html[data-theme="dark"] .radio-option:hover,
    html[data-bs-theme="dark"] .radio-option:hover,
    body.dark-mode .radio-option:hover,
    [data-pms-theme="dark"] .radio-option:hover,
    [data-theme="dark"] .radio-option:hover,
    .dark-mode .radio-option:hover {
        background: #1A2247 !important;
        border-color: rgba(79, 131, 255, 0.35) !important;
    }

    html[data-pms-theme="dark"] .radio-option:has(input:checked),
    html[data-theme="dark"] .radio-option:has(input:checked),
    html[data-bs-theme="dark"] .radio-option:has(input:checked),
    body.dark-mode .radio-option:has(input:checked),
    [data-pms-theme="dark"] .radio-option:has(input:checked),
    [data-theme="dark"] .radio-option:has(input:checked),
    .dark-mode .radio-option:has(input:checked) {
        background: rgba(47, 107, 255, 0.22) !important;
        border-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .radio-label,
    html[data-theme="dark"] .radio-label,
    html[data-bs-theme="dark"] .radio-label,
    body.dark-mode .radio-label,
    [data-pms-theme="dark"] .radio-label,
    [data-theme="dark"] .radio-label,
    .dark-mode .radio-label {
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .radio-option input[type="radio"]:checked + .radio-label,
    html[data-theme="dark"] .radio-option input[type="radio"]:checked + .radio-label,
    body.dark-mode .radio-option input[type="radio"]:checked + .radio-label,
    [data-theme="dark"] .radio-option input[type="radio"]:checked + .radio-label {
        color: #93C5FD !important;
        font-weight: 700 !important;
    }

    html[data-pms-theme="dark"] .checkbox-option,
    html[data-theme="dark"] .checkbox-option,
    html[data-bs-theme="dark"] .checkbox-option,
    html.dark .checkbox-option,
    body[data-pms-theme="dark"] .checkbox-option,
    body[data-theme="dark"] .checkbox-option,
    body[data-bs-theme="dark"] .checkbox-option,
    body.dark-mode .checkbox-option,
    body.dark .checkbox-option,
    [data-pms-theme="dark"] .checkbox-option,
    [data-theme="dark"] .checkbox-option,
    [data-bs-theme="dark"] .checkbox-option,
    .dark-mode .checkbox-option {
        background: #141B3D !important;
        border: 2px solid rgba(79, 131, 255, 0.2) !important;
    }

    html[data-pms-theme="dark"] .checkbox-option:hover,
    html[data-theme="dark"] .checkbox-option:hover,
    html[data-bs-theme="dark"] .checkbox-option:hover,
    body.dark-mode .checkbox-option:hover,
    [data-pms-theme="dark"] .checkbox-option:hover,
    [data-theme="dark"] .checkbox-option:hover,
    .dark-mode .checkbox-option:hover {
        background: #1A2247 !important;
        border-color: rgba(79, 131, 255, 0.35) !important;
    }

    html[data-pms-theme="dark"] .checkbox-option:has(input:checked),
    html[data-theme="dark"] .checkbox-option:has(input:checked),
    html[data-bs-theme="dark"] .checkbox-option:has(input:checked),
    body.dark-mode .checkbox-option:has(input:checked),
    [data-pms-theme="dark"] .checkbox-option:has(input:checked),
    [data-theme="dark"] .checkbox-option:has(input:checked),
    .dark-mode .checkbox-option:has(input:checked) {
        background: rgba(47, 107, 255, 0.22) !important;
        border-color: #60A5FA !important;
    }

    html[data-pms-theme="dark"] .checkbox-option input[type="checkbox"],
    html[data-theme="dark"] .checkbox-option input[type="checkbox"],
    body.dark-mode .checkbox-option input[type="checkbox"] {
        background-color: #141B3D !important;
        border-color: rgba(79, 131, 255, 0.4) !important;
        accent-color: #2F6BFF !important;
    }

    html[data-pms-theme="dark"] .checkbox-label,
    html[data-theme="dark"] .checkbox-label,
    html[data-bs-theme="dark"] .checkbox-label,
    body.dark-mode .checkbox-label,
    [data-pms-theme="dark"] .checkbox-label,
    [data-theme="dark"] .checkbox-label,
    .dark-mode .checkbox-label {
        color: #CBD5E1 !important;
    }

    html[data-pms-theme="dark"] .form-actions,
    html[data-theme="dark"] .form-actions,
    html[data-bs-theme="dark"] .form-actions,
    body.dark-mode .form-actions,
    [data-pms-theme="dark"] .form-actions,
    [data-theme="dark"] .form-actions,
    .dark-mode .form-actions {
        border-color: rgba(79, 131, 255, 0.18) !important;
    }

    html[data-pms-theme="dark"] .btn-submit,
    html[data-theme="dark"] .btn-submit,
    body.dark-mode .btn-submit {
        background: linear-gradient(135deg, #2563EB, #1D4ED8) !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35) !important;
    }

    html[data-pms-theme="dark"] .btn-submit:hover,
    html[data-theme="dark"] .btn-submit:hover,
    body.dark-mode .btn-submit:hover {
        background: linear-gradient(135deg, #3B82F6, #2563EB) !important;
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.5) !important;
    }

    html[data-pms-theme="dark"] .btn-cancel,
    html[data-theme="dark"] .btn-cancel,
    html[data-bs-theme="dark"] .btn-cancel,
    body.dark-mode .btn-cancel,
    [data-pms-theme="dark"] .btn-cancel,
    [data-theme="dark"] .btn-cancel,
    .dark-mode .btn-cancel {
        background: #141B3D !important;
        color: #CBD5E1 !important;
        border: 2px solid rgba(79, 131, 255, 0.25) !important;
    }

    html[data-pms-theme="dark"] .btn-cancel:hover,
    html[data-theme="dark"] .btn-cancel:hover,
    body.dark-mode .btn-cancel:hover,
    [data-theme="dark"] .btn-cancel:hover {
        background: #1A2247 !important;
        color: #FFFFFF !important;
        border-color: rgba(79, 131, 255, 0.4) !important;
    }

    html[data-pms-theme="dark"] .employee-status-dot,
    html[data-theme="dark"] .employee-status-dot,
    body.dark-mode .employee-status-dot {
        border-color: #141B3D !important;
    }

    html[data-pms-theme="dark"] .employee-avatar,
    html[data-theme="dark"] .employee-avatar,
    body.dark-mode .employee-avatar {
        border-color: #141B3D !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle other location field based on work_from_type selection
    const workFromSelect = document.getElementById('work_from_type');
    const otherLocationDiv = document.getElementById('other_location_div');

    if (workFromSelect && otherLocationDiv) {
        workFromSelect.addEventListener('change', function() {
            if (this.value === 'other') {
                otherLocationDiv.classList.remove('hidden');
            } else {
                otherLocationDiv.classList.add('hidden');
            }
        });
    }

    // Status radio sync - when status changes, update radio buttons
    const statusSelect = document.querySelector('select[name="status"]');
    const lateRadios = document.querySelectorAll('input[name="late"]');
    const halfDayRadios = document.querySelectorAll('input[name="half_day"]');

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const value = this.value;

            // Update late radios
            lateRadios.forEach(radio => {
                if (value === 'late') {
                    radio.value === 'yes' ? radio.checked = true : radio.checked = false;
                } else {
                    radio.value === 'no' ? radio.checked = true : radio.checked = false;
                }
            });

            // Update half day radios
            halfDayRadios.forEach(radio => {
                if (value === 'half_day') {
                    radio.value === 'yes' ? radio.checked = true : radio.checked = false;
                } else {
                    radio.value === 'no' ? radio.checked = true : radio.checked = false;
                }
            });
        });
    }

    // Radio buttons update status select
    lateRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'yes' && this.checked && statusSelect) {
                statusSelect.value = 'late';
            } else if (this.value === 'no' && this.checked && statusSelect && statusSelect.value === 'late') {
                statusSelect.value = 'present';
            }
        });
    });

    halfDayRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'yes' && this.checked && statusSelect) {
                statusSelect.value = 'half_day';
            } else if (this.value === 'no' && this.checked && statusSelect && statusSelect.value === 'half_day') {
                statusSelect.value = 'present';
            }
        });
    });
});
</script>
