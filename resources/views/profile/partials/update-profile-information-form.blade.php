@php
    $employeeDetail = $user->employeeDetail;
    $isEmployeeProfile = ($user->role ?? '') === 'employee';

    $fieldValue = function (string $field, $fallback = '') use ($user, $employeeDetail) {
        return old($field, $user->{$field} ?? $employeeDetail?->{$field} ?? $fallback);
    };

    $dateValue = function (string $field) use ($fieldValue) {
        $value = $fieldValue($field);

        if (blank($value)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return $value;
        }
    };

    $profileImage = $user->profile_image ? asset($user->profile_image) : asset('admin/assets/img/avatars/1.png');
    $governmentIdCard = $employeeDetail?->government_id_card ?? $user->government_id_card;
    $governmentIdStatus = $employeeDetail?->government_id_verification_status ?? $user->government_id_verification_status;
@endphp

<section>
    <header class="mb-4">
        <h5 class="fw-semibold mb-1">Profile Information</h5>
        <p class="text-muted mb-0">Update your account details and personal information.</p>
    </header>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success" role="alert">
            Profile updated successfully.
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="row g-3 needs-validation" novalidate>
        @csrf
        @method('PATCH')

        <div class="col-12 d-flex align-items-center gap-3 mb-2">
            <img src="{{ $profileImage }}" alt="Profile image" class="rounded-circle border" style="width: 88px; height: 88px; object-fit: cover;">
            <div class="flex-grow-1">
                <label for="profile_image" class="form-label">Profile Image</label>
                <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif">
                @error('profile_image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required maxlength="255">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required maxlength="255">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if ($isEmployeeProfile)
            <div class="col-md-6">
                <label for="designation" class="form-label">Designation</label>
                <select name="designation" id="designation" class="form-select @error('designation') is-invalid @enderror">
                    <option value="">Select designation</option>
                    @foreach ($designations as $designation)
                        <option value="{{ $designation->name }}" @selected(old('designation', $user->designation) === $designation->name)>
                            {{ $designation->name }}
                        </option>
                    @endforeach
                </select>
                @error('designation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <div class="col-md-6">
            <label for="mobile" class="form-label">Mobile</label>
            @php
                $profCountries = \App\Models\Country::getAllWithPhoneCodes();
                $profMob = $fieldValue('mobile');
                $profCode = '+91';
                if ($profMob && str_starts_with($profMob, '+')) {
                    foreach($profCountries as $c) {
                        if (str_starts_with($profMob, $c->phone_code)) {
                            $profCode = $c->phone_code;
                            $profMob = trim(substr($profMob, strlen($c->phone_code)));
                            break;
                        }
                    }
                }
                $selProfCode = old('mobile_country_code', $profCode);
            @endphp
            <div class="input-group">
                <select name="mobile_country_code" id="mobile_country_code" class="form-select" style="max-width: 110px;">
                    @foreach($profCountries as $c)
                        <option value="{{ $c->phone_code }}" {{ $selProfCode == $c->phone_code ? 'selected' : '' }}>
                            {{ $c->iso_code ? $c->iso_code . ' ' : '' }}({{ $c->phone_code }})
                        </option>
                    @endforeach
                </select>
                <input type="text" name="mobile" id="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $profMob) }}" maxlength="20" placeholder="Mobile number">
            </div>
            @error('mobile')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="gender" class="form-label">Gender</label>
            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror">
                <option value="">Select gender</option>
                <option value="male" @selected($fieldValue('gender') === 'male')>Male</option>
                <option value="female" @selected($fieldValue('gender') === 'female')>Female</option>
                <option value="other" @selected($fieldValue('gender') === 'other')>Other</option>
            </select>
            @error('gender')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" name="dob" id="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ $dateValue('dob') }}" max="{{ now()->format('Y-m-d') }}">
            @error('dob')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="government_id_card" class="form-label">Government ID Card</label>
            <input type="file" name="government_id_card" id="government_id_card" class="form-control @error('government_id_card') is-invalid @enderror" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            @if ($governmentIdCard)
                <small class="text-muted d-block mt-1">Current: <a href="{{ asset($governmentIdCard) }}" target="_blank">view image</a></small>
            @else
                <small class="text-muted d-block mt-1">Required when changing date of birth.</small>
            @endif
            @if ($governmentIdStatus)
                <small class="d-block mt-1">
                    Verification:
                    <span class="badge {{ $governmentIdStatus === 'approved' ? 'bg-success' : ($governmentIdStatus === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                        {{ str_replace('_', ' ', $governmentIdStatus) }}
                    </span>
                </small>
            @endif
            @error('government_id_card')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="marital_status" class="form-label">Marital Status</label>
            <select name="marital_status" id="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                <option value="">Select status</option>
                <option value="single" @selected($fieldValue('marital_status') === 'single')>Single</option>
                <option value="married" @selected($fieldValue('marital_status') === 'married')>Married</option>
            </select>
            @error('marital_status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="country" class="form-label">Country</label>
            <input type="text" name="country" id="country" class="form-control @error('country') is-invalid @enderror" value="{{ $fieldValue('country') }}" maxlength="100">
            @error('country')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="language" class="form-label">Language</label>
            <input type="text" name="language" id="language" class="form-control @error('language') is-invalid @enderror" value="{{ $fieldValue('language') }}" maxlength="50">
            @error('language')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if ($isEmployeeProfile)
            <div class="col-md-6">
                <label for="slack_id" class="form-label">Slack ID</label>
                <input type="text" name="slack_id" id="slack_id" class="form-control @error('slack_id') is-invalid @enderror" value="{{ old('slack_id', $user->slack_id ?? $employeeDetail?->slack_member_id) }}" maxlength="100">
                @error('slack_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <div class="col-12">
            <label for="address" class="form-label">Address</label>
            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ $fieldValue('address') }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="about" class="form-label">About</label>
            <textarea name="about" id="about" class="form-control @error('about') is-invalid @enderror" rows="3">{{ $fieldValue('about') }}</textarea>
            @error('about')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <div class="form-check form-switch">
                <input type="hidden" name="email_notify" value="0">
                <input type="checkbox" name="email_notify" id="email_notify" class="form-check-input @error('email_notify') is-invalid @enderror" value="1" @checked((bool) old('email_notify', $user->email_notify))>
                <label for="email_notify" class="form-check-label">Email Notifications</label>
                @error('email_notify')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-check form-switch">
                <input type="hidden" name="google_calendar" value="0">
                <input type="checkbox" name="google_calendar" id="google_calendar" class="form-check-input @error('google_calendar') is-invalid @enderror" value="1" @checked((bool) old('google_calendar', $user->google_calendar))>
                <label for="google_calendar" class="form-check-label">Google Calendar</label>
                @error('google_calendar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</section>
