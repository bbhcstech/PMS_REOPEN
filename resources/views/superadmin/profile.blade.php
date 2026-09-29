@extends('layouts.superadmin')

@section('page_title', 'Profile')
@section('page_subtitle', 'Micro Poem Admin / Users / Profile')

@push('styles')
<style>
  /* ===== PROFILE PAGE — THEME-AWARE ===== */
  .profile-container {
    max-width: 1100px;
    margin: 0 auto;
    padding-bottom: 40px;
  }

  .profile-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 16px;
    box-shadow: var(--card-shadow-md);
    overflow: hidden;
  }

  .profile-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 28px;
    border-bottom: 1px solid var(--border-subtle);
    background: var(--bg-surface-subtle);
  }

  .profile-card-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -0.2px;
  }

  .profile-card-close {
    color: var(--text-muted);
    font-size: 24px;
    line-height: 1;
    transition: color 0.2s ease, background 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
  }

  .profile-card-close:hover {
    color: var(--text-main);
    background: var(--bg-surface-hover);
  }

  .profile-card-body {
    padding: 28px;
  }

  .profile-sec-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 4px;
  }

  .profile-sec-subtitle {
    font-size: 13.5px;
    color: var(--text-muted);
    margin-bottom: 28px;
  }

  /* AVATAR */
  .avatar-upload-wrap {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 28px;
  }

  .avatar-preview {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(47, 107, 255, 0.45);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    background: var(--bg-surface-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: 800;
    color: var(--brand-primary);
    flex-shrink: 0;
  }

  /* FILE INPUT */
  .file-input-box {
    flex: 1;
    max-width: 500px;
    display: flex;
    align-items: center;
    background: var(--bg-surface-subtle);
    border: 1px solid var(--border-strong);
    border-radius: 10px;
    padding: 6px 12px;
    gap: 12px;
    cursor: pointer;
  }

  .file-input-box label {
    background: var(--bg-surface-hover);
    color: var(--text-body);
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--border-strong);
    transition: all 0.2s ease;
    white-space: nowrap;
    margin-bottom: 0;
  }

  .file-input-box label:hover {
    border-color: var(--brand-primary);
    color: var(--brand-primary);
  }

  .file-input-box span {
    font-size: 13px;
    color: var(--text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .file-input-box input[type="file"] {
    display: none;
  }

  /* FORM GRID */
  .profile-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px 24px;
  }

  @media (max-width: 768px) {
    .profile-form-grid {
      grid-template-columns: 1fr;
    }
  }

  .form-group-full {
    grid-column: 1 / -1;
  }

  .profile-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-body);
    margin-bottom: 8px;
  }

  /* FORM CONTROLS */
  .profile-control {
    width: 100%;
    background: var(--bg-surface-subtle) !important;
    border: 1px solid var(--border-strong) !important;
    border-radius: 10px !important;
    padding: 10px 14px !important;
    font-size: 14px !important;
    color: var(--text-main) !important;
    outline: none !important;
    transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
    font-family: inherit !important;
  }

  .profile-control:focus {
    border-color: var(--brand-primary) !important;
    box-shadow: 0 0 0 3px var(--emerald-glow) !important;
  }

  select.profile-control option {
    background: var(--bg-surface);
    color: var(--text-main);
  }

  textarea.profile-control {
    resize: vertical;
    min-height: 100px;
  }

  .field-help-text {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 6px;
    font-weight: 500;
  }

  /* TOGGLES */
  .toggles-row {
    display: flex;
    align-items: center;
    gap: 36px;
    margin-top: 28px;
    padding-top: 10px;
    grid-column: 1 / -1;
    flex-wrap: wrap;
  }

  .toggle-item {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    user-select: none;
  }

  .toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
    background: var(--bg-surface-hover);
    border-radius: 20px;
    transition: background 0.25s ease;
    border: 1px solid var(--border-strong);
  }

  .toggle-switch::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    background: var(--text-muted);
    border-radius: 50%;
    transition: transform 0.25s ease, background 0.25s ease;
  }

  .toggle-checkbox { display: none; }

  .toggle-checkbox:checked + .toggle-switch {
    background: var(--brand-primary);
    border-color: var(--brand-primary);
  }

  .toggle-checkbox:checked + .toggle-switch::after {
    transform: translateX(20px);
    background: #ffffff;
  }

  .toggle-label-text {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-body);
  }

  /* FOOTER */
  .profile-form-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 20px;
    border-top: 1px solid var(--border-subtle);
    grid-column: 1 / -1;
  }

  .btn-save-profile {
    background: linear-gradient(135deg, var(--brand-primary), var(--brand-primary-hover));
    color: #ffffff;
    font-weight: 700;
    font-size: 14px;
    padding: 11px 28px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(47, 107, 255, 0.35);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: inherit;
  }

  .btn-save-profile:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(47, 107, 255, 0.45);
    opacity: 0.9;
  }

  /* ===== LIGHT THEME EXPLICIT OVERRIDES ===== */
  html[data-pms-theme="light"] .profile-card,
  html[data-theme="light"] .profile-card {
    background: #ffffff;
    border-color: rgba(16, 20, 44, 0.10);
    box-shadow: 0 8px 24px -4px rgba(16, 20, 44, 0.06);
  }

  html[data-pms-theme="light"] .profile-card-header,
  html[data-theme="light"] .profile-card-header {
    background: #f8fafc;
    border-bottom-color: rgba(16, 20, 44, 0.08);
  }

  html[data-pms-theme="light"] .profile-control,
  html[data-theme="light"] .profile-control {
    background: #f8fafc !important;
    border-color: rgba(16, 20, 44, 0.14) !important;
    color: #10142C !important;
  }

  html[data-pms-theme="light"] .profile-control:focus,
  html[data-theme="light"] .profile-control:focus {
    border-color: #2F6BFF !important;
    box-shadow: 0 0 0 3px rgba(47, 107, 255, 0.12) !important;
  }

  html[data-pms-theme="light"] select.profile-control option,
  html[data-theme="light"] select.profile-control option {
    background: #ffffff;
    color: #10142C;
  }

  html[data-pms-theme="light"] .file-input-box,
  html[data-theme="light"] .file-input-box {
    background: #f1f5f9;
    border-color: rgba(16, 20, 44, 0.12);
  }

  html[data-pms-theme="light"] .file-input-box label,
  html[data-theme="light"] .file-input-box label {
    background: #e2e8f0;
    color: #334155;
    border-color: rgba(16, 20, 44, 0.10);
  }

  html[data-pms-theme="light"] .toggle-switch,
  html[data-theme="light"] .toggle-switch {
    background: #e2e8f0;
    border-color: rgba(16, 20, 44, 0.12);
  }

  html[data-pms-theme="light"] .toggle-switch::after,
  html[data-theme="light"] .toggle-switch::after {
    background: #94a3b8;
  }

  html[data-pms-theme="light"] .profile-form-footer,
  html[data-theme="light"] .profile-form-footer {
    border-top-color: rgba(16, 20, 44, 0.08);
  }

  html[data-pms-theme="light"] .avatar-preview,
  html[data-theme="light"] .avatar-preview {
    background: #eff6ff;
  }
</style>
@endpush

@section('content')
<div class="profile-container">

  @if(session('success'))
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; padding: 14px 18px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
      <i class="bx bx-check-circle" style="font-size: 20px;"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  <div class="profile-card">
    <!-- CARD HEADER -->
    <div class="profile-card-header">
      <div class="profile-card-title">Profile</div>
      <a href="{{ Route::has('superadmin.dashboard') ? route('superadmin.dashboard') : url('/superadmin') }}" class="profile-card-close" title="Close Profile">&times;</a>
    </div>

    <!-- CARD BODY -->
    <div class="profile-card-body">
      
      <!-- SECTION TITLE -->
      <div class="profile-sec-title">Profile Information</div>
      <div class="profile-sec-subtitle">Update your account details and personal information.</div>

      <!-- FORM -->
      <form action="{{ route('super-admin.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- PROFILE IMAGE UPLOAD -->
        <div class="avatar-upload-wrap">
          @if(!empty($user->profile_image) && file_exists(public_path($user->profile_image)))
            <img src="{{ asset($user->profile_image) }}" alt="Profile Avatar" class="avatar-preview" id="avatarPreviewImg" />
          @elseif(!empty($user->image) && file_exists(public_path($user->image)))
            <img src="{{ asset($user->image) }}" alt="Profile Avatar" class="avatar-preview" id="avatarPreviewImg" />
          @else
            <div class="avatar-preview" id="avatarPreviewFallback">
              {{ strtoupper(substr($user->name ?? 'SA', 0, 2)) }}
            </div>
          @endif

          <div>
            <label class="profile-label">Profile Image</label>
            <div class="file-input-box" onclick="document.getElementById('profileImageInput').click();">
              <label for="profileImageInput">Choose File</label>
              <span id="profileFileName">No file chosen</span>
              <input type="file" name="profile_image" id="profileImageInput" accept="image/*" onchange="updateFileName(this, 'profileFileName', 'avatarPreviewImg', 'avatarPreviewFallback')" />
            </div>
          </div>
        </div>

        <!-- FORM GRID -->
        <div class="profile-form-grid">
          
          <!-- NAME -->
          <div>
            <label class="profile-label">Name</label>
            <input type="text" name="name" class="profile-control" value="{{ old('name', $user->name ?? 'Admin User') }}" required placeholder="Admin User" />
          </div>

          <!-- EMAIL -->
          <div>
            <label class="profile-label">Email</label>
            <input type="email" name="email" class="profile-control" value="{{ old('email', $user->email ?? 'admin@company.com') }}" required placeholder="admin@company.com" />
          </div>

          <!-- MOBILE -->
          <div>
            <label class="profile-label">Mobile</label>
            @php
              $fullMobile = old('mobile', $user->mobile ?? '');
              $countryCode = '+91';
              $mobileNum = $fullMobile;
              // Extract country code if starts with '+'
              if($fullMobile && preg_match('/^(\+\d{1,4})\s*[-\s]?(.*)$/', $fullMobile, $matches)) {
                  $countryCode = $matches[1];
                  $mobileNum = $matches[2];
              }
            @endphp
            <div style="display: flex;">
              <select id="profile_country_code" class="profile-control" style="width: 100px; flex-shrink: 0; border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0; background-position: right 0.25rem center; padding-right: 20px;">
                <option value="+91" {{ $countryCode == '+91' ? 'selected' : '' }}>+91 (IN)</option>
                <option value="+1" {{ $countryCode == '+1' ? 'selected' : '' }}>+1 (US)</option>
                <option value="+44" {{ $countryCode == '+44' ? 'selected' : '' }}>+44 (UK)</option>
                <option value="+61" {{ $countryCode == '+61' ? 'selected' : '' }}>+61 (AU)</option>
                <option value="+971" {{ $countryCode == '+971' ? 'selected' : '' }}>+971 (AE)</option>
                <option value="+81" {{ $countryCode == '+81' ? 'selected' : '' }}>+81 (JP)</option>
                <option value="+49" {{ $countryCode == '+49' ? 'selected' : '' }}>+49 (DE)</option>
                <option value="+33" {{ $countryCode == '+33' ? 'selected' : '' }}>+33 (FR)</option>
              </select>
              <input type="text" id="profile_mobile_number" class="profile-control" value="{{ $mobileNum }}" placeholder="Enter mobile number" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" />
              <input type="hidden" name="mobile" id="profile_mobile_hidden" value="{{ $fullMobile }}">
            </div>
          </div>
          <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ccSelect = document.getElementById('profile_country_code');
                const mobileInput = document.getElementById('profile_mobile_number');
                const hiddenMobile = document.getElementById('profile_mobile_hidden');
                
                function updateMobile() {
                    const num = mobileInput.value.replace(/[^0-9]/g, '');
                    if (num === '') {
                        hiddenMobile.value = '';
                    } else {
                        hiddenMobile.value = ccSelect.value + ' ' + num;
                    }
                }
                
                if (ccSelect && mobileInput) {
                    ccSelect.addEventListener('change', updateMobile);
                    mobileInput.addEventListener('input', updateMobile);
                }
            });
          </script>

          <!-- GENDER -->
          <div>
            <label class="profile-label">Gender</label>
            <select name="gender" class="profile-control">
              <option value="">Select gender</option>
              <option value="Male" {{ old('gender', $user->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
              <option value="Female" {{ old('gender', $user->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
              <option value="Other" {{ old('gender', $user->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
          </div>

          <!-- DATE OF BIRTH -->
          <div>
            <label class="profile-label">Date of Birth</label>
            <input type="date" name="date_of_birth" class="profile-control" value="{{ old('date_of_birth', isset($user->date_of_birth) ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '') }}" />
          </div>

          <!-- GOVERNMENT ID CARD -->
          <div>
            <label class="profile-label">Government ID Card</label>
            <div class="file-input-box" onclick="document.getElementById('govtIdInput').click();">
              <label for="govtIdInput">Choose File</label>
              <span id="govtIdFileName">No file chosen</span>
              <input type="file" name="govt_id_card" id="govtIdInput" accept=".pdf,image/*" onchange="updateFileName(this, 'govtIdFileName')" />
            </div>
            <div class="field-help-text">Required when changing date of birth.</div>
          </div>

          <!-- MARITAL STATUS -->
          <div>
            <label class="profile-label">Marital Status</label>
            <select name="marital_status" class="profile-control">
              <option value="">Select status</option>
              <option value="Single" {{ old('marital_status', $user->marital_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
              <option value="Married" {{ old('marital_status', $user->marital_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
              <option value="Other" {{ old('marital_status', $user->marital_status ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
          </div>

          <!-- COUNTRY -->
          <div>
            <label class="profile-label">Country</label>
            <input type="text" name="country" class="profile-control" value="{{ old('country', $user->country ?? '') }}" placeholder="Enter country" />
          </div>

          <!-- LANGUAGE -->
          <div class="form-group-full">
            <label class="profile-label">Language</label>
            <input type="text" name="language" class="profile-control" value="{{ old('language', $user->language ?? '') }}" placeholder="Enter language preference" />
          </div>

          <!-- ADDRESS -->
          <div class="form-group-full">
            <label class="profile-label">Address</label>
            <textarea name="address" class="profile-control" rows="3" placeholder="Enter address">{{ old('address', $user->address ?? '') }}</textarea>
          </div>

          <!-- ABOUT -->
          <div class="form-group-full">
            <label class="profile-label">About</label>
            <textarea name="about" class="profile-control" rows="3" placeholder="Write about yourself">{{ old('about', $user->about ?? '') }}</textarea>
          </div>

          <!-- TOGGLES -->
          <div class="toggles-row">
            <label class="toggle-item">
              <input type="checkbox" name="email_notifications" class="toggle-checkbox" value="1" {{ old('email_notifications', $user->email_notifications ?? true) ? 'checked' : '' }} />
              <div class="toggle-switch"></div>
              <span class="toggle-label-text">Email Notifications</span>
            </label>

            <label class="toggle-item">
              <input type="checkbox" name="google_calendar" class="toggle-checkbox" value="1" {{ old('google_calendar', $user->google_calendar ?? false) ? 'checked' : '' }} />
              <div class="toggle-switch"></div>
              <span class="toggle-label-text">Google Calendar</span>
            </label>
          </div>

          <!-- SUBMIT BUTTON -->
          <div class="profile-form-footer">
            <button type="submit" class="btn-save-profile">
              Save Changes
            </button>
          </div>

        </div>
      </form>

    </div>
  </div>
</div>

<script>
function updateFileName(input, spanId, imgId, fallbackId) {
  if (input.files && input.files[0]) {
    document.getElementById(spanId).innerText = input.files[0].name;
    if (imgId) {
      var reader = new FileReader();
      reader.onload = function(e) {
        var img = document.getElementById(imgId);
        var fallback = document.getElementById(fallbackId);
        if (!img && fallback) {
          img = document.createElement('img');
          img.id = imgId;
          img.className = 'avatar-preview';
          fallback.parentNode.replaceChild(img, fallback);
        }
        if (img) {
          img.src = e.target.result;
        }
      }
      reader.readAsDataURL(input.files[0]);
    }
  }
}
</script>
@endsection
