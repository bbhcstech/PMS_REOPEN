@php
    $useOld = (string) old('_editing_account_id', 'none') === (string) ($account?->id ?? 0);
    $value = fn ($key, $fallback = '') => $useOld ? old($key, $fallback) : $fallback;
@endphp
<input type="hidden" name="_editing_account_id" value="{{ $account?->id ?? 0 }}">
<div class="row g-3">
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}Name">Name</label><input id="{{ $fieldPrefix }}Name" class="form-control" name="name" value="{{ $value('name', $account?->name) }}" required maxlength="255"></div>
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}Email">Email</label><input id="{{ $fieldPrefix }}Email" type="email" class="form-control" name="email" value="{{ $value('email', $account?->email) }}" required maxlength="255" autocomplete="off"></div>
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}Role">Company role</label><select id="{{ $fieldPrefix }}Role" class="form-select" name="company_staff_role_id" required>
        <option value="">Select company role</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected((string)$value('company_staff_role_id', $account?->company_staff_role_id) === (string)$role->id)>{{ $role->name }} ({{ ucfirst($role->access_role) }})</option>@endforeach
    </select></div>
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}Designation">Designation / Level</label><select id="{{ $fieldPrefix }}Designation" class="form-select" name="designation_id" required>
        <option value="">Select designation</option>@foreach($designations as $designation)<option value="{{ $designation->id }}" @selected((string)$value('designation_id', $account?->employeeDetail?->designation_id) === (string)$designation->id)>{{ $designation->name }} · Level {{ $designation->level }}</option>@endforeach
    </select></div>
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}Joining">Joining date</label><input id="{{ $fieldPrefix }}Joining" class="form-control" type="date" name="joining_date" value="{{ $value('joining_date', $account?->employeeDetail?->joining_date ? \Carbon\Carbon::parse($account->employeeDetail->joining_date)->format('Y-m-d') : '') }}" required></div>
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}Login">Login access</label><select id="{{ $fieldPrefix }}Login" class="form-select" name="login_allowed" required><option value="1" @selected((string)$value('login_allowed', $account ? (int)$account->login_allowed : 1) === '1')>Enabled</option><option value="0" @selected((string)$value('login_allowed', $account ? (int)$account->login_allowed : 1) === '0')>Disabled</option></select></div>
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}Password">{{ $account ? 'New password (optional)' : 'Password' }}</label>
        <div class="input-group upper-level-password-group">
            <input id="{{ $fieldPrefix }}Password" type="password" class="form-control" name="password" minlength="8" maxlength="255" autocomplete="new-password" @required(!$account)>
            <button type="button" class="btn upper-level-password-toggle" data-password-target="{{ $fieldPrefix }}Password" aria-label="Show password" title="Show password"><i class="bx bx-show"></i></button>
        </div>
    </div>
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}PasswordConfirm">Confirm password</label>
        <div class="input-group upper-level-password-group">
            <input id="{{ $fieldPrefix }}PasswordConfirm" type="password" class="form-control" name="password_confirmation" minlength="8" maxlength="255" autocomplete="new-password" @required(!$account)>
            <button type="button" class="btn upper-level-password-toggle" data-password-target="{{ $fieldPrefix }}PasswordConfirm" aria-label="Show password" title="Show password"><i class="bx bx-show"></i></button>
        </div>
    </div>
</div>

@once
<style>
    .upper-level-password-group { flex-wrap: nowrap; }
    .upper-level-password-group > .form-control { border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important; border-right: 0 !important; min-width: 0; }
    .upper-level-password-toggle {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 48px; padding: 0 14px; font-size: 1.15rem; line-height: 1;
        color: var(--bx-text-muted, #64748b);
        background: var(--bx-surface, #ffffff);
        border: 1px solid var(--bs-border-color, #d9dee3) !important; border-left: 0 !important;
        border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;
        border-top-right-radius: var(--bs-border-radius, 0.375rem) !important; border-bottom-right-radius: var(--bs-border-radius, 0.375rem) !important;
    }
    .upper-level-password-toggle:hover, .upper-level-password-toggle:focus-visible { color: var(--bx-blue, #2f6bff); }
    .upper-level-password-group:focus-within > .form-control,
    .upper-level-password-group:focus-within > .upper-level-password-toggle { border-color: var(--bx-blue, #2f6bff) !important; }
    :is([data-pms-theme="dark"], [data-theme="dark"], [data-bs-theme="dark"], .dark-mode, .dark) .upper-level-password-toggle {
        color: #9AA7D1; background: var(--bx-surface-2, #111839); border-color: rgba(238, 241, 251, 0.12) !important;
    }
    :is([data-pms-theme="dark"], [data-theme="dark"], [data-bs-theme="dark"], .dark-mode, .dark) .upper-level-password-toggle:hover { color: #EEF1FB; }
</style>
<script>
    // Show / hide password; delegated so it works for every add/edit form and after live refreshes.
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('.upper-level-password-toggle');
        if (!toggle) return;
        event.preventDefault();
        const input = document.getElementById(toggle.dataset.passwordTarget);
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        const label = show ? 'Hide password' : 'Show password';
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
        toggle.innerHTML = '<i class="bx ' + (show ? 'bx-hide' : 'bx-show') + '"></i>';
        input.focus({ preventScroll: true });
    });
</script>
@endonce
