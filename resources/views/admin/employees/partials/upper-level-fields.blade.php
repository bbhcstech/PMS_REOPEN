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
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}Password">{{ $account ? 'New password (optional)' : 'Password' }}</label><input id="{{ $fieldPrefix }}Password" type="password" class="form-control" name="password" minlength="8" maxlength="255" autocomplete="new-password" @required(!$account)></div>
    <div class="col-md-6"><label class="form-label" for="{{ $fieldPrefix }}PasswordConfirm">Confirm password</label><input id="{{ $fieldPrefix }}PasswordConfirm" type="password" class="form-control" name="password_confirmation" minlength="8" maxlength="255" autocomplete="new-password" @required(!$account)></div>
</div>
