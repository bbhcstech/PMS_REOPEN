@extends('layouts.developer')

@section('title', 'My Profile')
@section('page_title', 'Developer Profile')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- TOP HEADER -->
    <div class="dev-card" style="margin-bottom: 0; padding: 28px;">
        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <div style="position: relative;">
                <div id="avatarContainer" style="width: 86px; height: 86px; border-radius: 50%; background: linear-gradient(135deg, #059669, #047857); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 30px; font-weight: 800; border: 3px solid #ffffff; box-shadow: var(--shadow-md); overflow: hidden; flex-shrink: 0;">
                    @if(!empty($dev->profile_image) && file_exists(public_path($dev->profile_image)))
                        <img id="avatarPreview" src="{{ asset($dev->profile_image) }}" alt="{{ $dev->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span id="avatarInitials">{{ strtoupper(substr($dev->name ?? 'Dev', 0, 2)) }}</span>
                        <img id="avatarPreview" src="" alt="{{ $dev->name }}" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                    @endif
                </div>
                <button type="button" onclick="document.getElementById('profile_image_input').click()" style="position: absolute; bottom: 0; right: 0; width: 28px; height: 28px; border-radius: 50%; background: #10b981; color: #ffffff; border: 2px solid #ffffff; display: flex; align-items: center; justify-content: center; font-size: 15px; cursor: pointer; box-shadow: var(--shadow-sm);" title="Upload Profile Picture">
                    <i class="bx bx-camera"></i>
                </button>
            </div>

            <div style="flex: 1; min-width: 260px;">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <h1 style="font-size: 24px; font-weight: 800; color: var(--slate-dark);">{{ $dev->name }}</h1>
                    <span style="background: #ecfdf5; color: #059669; font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 14px; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669; display: inline-block;"></span>
                        {{ ucfirst($empDetail?->status ?? 'Available') }}
                    </span>
                </div>
                <p style="font-size: 14px; color: var(--primary); font-weight: 700; margin-top: 4px;">
                    {{ ucfirst($dev->designation ?? 'Full Stack Developer') }}
                </p>
                <span style="font-size: 12px; color: var(--slate-muted); font-family: monospace; display: block; margin-top: 6px; font-weight: 600;">
                    ID: {{ $empDetail->developer_id ?? ('DEV-' . str_pad($dev->id, 4, '0', STR_PAD_LEFT)) }} &bull; Joined: {{ !empty($empDetail->joining_date) ? \Carbon\Carbon::parse($empDetail->joining_date)->format('M Y') : ($dev->created_at ? $dev->created_at->format('M Y') : 'Aug 2026') }}
                </span>
            </div>
        </div>
    </div>

    <!-- PERFORMANCE OVERVIEW CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div class="dev-card" style="margin-bottom: 0; padding: 20px;">
            <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px;">Total Work Assigned</span>
            <div style="font-size: 26px; font-weight: 900; color: var(--slate-dark); margin-top: 6px; line-height: 1;">{{ $performance['total_assigned'] ?? 0 }} Tasks</div>
        </div>

        <div class="dev-card" style="margin-bottom: 0; padding: 20px;">
            <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px;">Tasks Completed</span>
            <div style="font-size: 26px; font-weight: 900; color: #059669; margin-top: 6px; line-height: 1;">{{ $performance['completed'] ?? 0 }} Tasks</div>
        </div>

        <div class="dev-card" style="margin-bottom: 0; padding: 20px;">
            <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px;">Task Completion Rate</span>
            <div style="font-size: 26px; font-weight: 900; color: #2563eb; margin-top: 6px; line-height: 1;">{{ $performance['completion_rate'] ?? 100 }}%</div>
        </div>

        <div class="dev-card" style="margin-bottom: 0; padding: 20px;">
            <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px;">Avg Completion Time</span>
            <div style="font-size: 26px; font-weight: 900; color: #7c3aed; margin-top: 6px; line-height: 1;">{{ $performance['avg_completion_time'] ?? 1.5 }} Days</div>
        </div>
    </div>

    <!-- MAIN PROFILE GRID -->
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">

        <!-- LEFT COLUMN: READ-ONLY SYSTEM METADATA -->
        <div class="dev-card" style="margin-bottom: 0; padding: 26px;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-dark); margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
                Account & System Authority
            </h3>

            <div style="display: flex; flex-direction: column; gap: 16px; font-size: 13px;">
                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">FULL NAME</span>
                    <strong style="color: var(--slate-dark); font-size: 14px;">{{ $dev->name }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">LOGIN EMAIL</span>
                    <strong style="color: var(--slate-dark); font-family: monospace; font-size: 13px;">{{ $dev->email }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">PERSONAL EMAIL (CREDENTIALS)</span>
                    <strong style="color: var(--slate-dark); font-family: monospace; font-size: 13px;">{{ $dev->personal_email ?? $dev->email }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">DESIGNATION / ROLE</span>
                    <strong style="color: var(--primary); font-size: 13.5px;">{{ ucfirst($dev->designation ?? 'Developer') }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">DEVELOPER ID</span>
                    <strong style="font-family: monospace; color: var(--slate-dark); font-size: 13.5px;">{{ $empDetail->developer_id ?? ('DEV-' . str_pad($dev->id, 4, '0', STR_PAD_LEFT)) }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">EXPERIENCE LEVEL</span>
                    <strong style="color: var(--slate-dark); font-size: 13.5px;">{{ $empDetail->experience ?? '3+ Years' }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 800; display: block; text-transform: uppercase; letter-spacing: 0.5px;">ACCOUNT STATUS</span>
                    <strong style="color: #059669; font-size: 12px; font-weight: 800;">PERMANENT ACCOUNT &bull; VERIFIED</strong>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: AUTHORIZED EDITABLE FIELDS -->
        <div class="dev-card" style="margin-bottom: 0; padding: 26px;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-dark); margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
                Edit Authorized Profile Details
            </h3>

            <form method="POST" action="{{ route('developer.profile.update') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
                @csrf

                <!-- PROFILE PICTURE UPLOAD INPUT -->
                <div>
                    <label style="font-size: 13px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 8px;">
                        Upload Profile Picture:
                    </label>
                    <input type="file" id="profile_image_input" name="profile_image" accept="image/*" onchange="previewAvatar(event)" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px; background: #ffffff; cursor: pointer; box-shadow: var(--shadow-xs);">
                    <span style="font-size: 12px; color: var(--slate-muted); margin-top: 6px; display: block;">Supports PNG, JPG, JPEG, GIF, WebP (Max 5MB)</span>
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 8px;">
                        Current Status / Availability:
                    </label>
                    <select name="status" style="width: 100%; padding: 11px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13.5px; font-weight: 700; background: #ffffff; color: var(--slate-dark); outline: none; box-shadow: var(--shadow-xs);">
                        <option value="Available" {{ (strtolower($empDetail?->status ?? '') === 'available' || empty($empDetail?->status)) ? 'selected' : '' }}>Available</option>
                        <option value="Busy" {{ strtolower($empDetail?->status ?? '') === 'busy' ? 'selected' : '' }}>Busy</option>
                        <option value="On Leave" {{ strtolower($empDetail?->status ?? '') === 'on_leave' || strtolower($empDetail?->status ?? '') === 'on leave' ? 'selected' : '' }}>On Leave</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 8px;">
                        Mobile Contact Number:
                    </label>
                    <input type="text" name="mobile" value="{{ old('mobile', $dev->mobile ?? $empDetail?->mobile) }}" placeholder="+91 98765 43210" style="width: 100%; padding: 11px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13.5px; font-weight: 500; outline: none; box-shadow: var(--shadow-xs);">
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 8px;">
                        Skills &amp; Tech Stack (Comma separated):
                    </label>
                    <input type="text" name="skills" value="{{ old('skills', $empDetail?->skills ?? implode(', ', $skillsArray)) }}" placeholder="Laravel, PHP, MySQL, REST API, Git, Vue.js" style="width: 100%; padding: 11px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13.5px; font-weight: 500; outline: none; box-shadow: var(--shadow-xs);">
                    <span style="font-size: 12px; color: var(--slate-muted); margin-top: 6px; display: block;">Example: Laravel, PHP, MySQL, REST API, Git</span>
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 8px;">
                        Professional Bio / Summary:
                    </label>
                    <textarea name="about" rows="4" style="width: 100%; padding: 11px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13.5px; font-family: inherit; line-height: 1.55; outline: none; box-shadow: var(--shadow-xs);" placeholder="Backend developer specializing in scalable Laravel applications and REST APIs.">{{ old('about', $dev->about ?? $empDetail?->about) }}</textarea>
                </div>

                <div style="padding-top: 10px; display: flex; justify-content: flex-end;">
                    <button type="submit" style="padding: 11px 26px; border-radius: var(--radius-md); background: var(--primary); color: #ffffff; border: none; font-size: 13.5px; font-weight: 700; cursor: pointer; box-shadow: var(--shadow-xs); transition: background 0.2s;" onmouseover="this.style.background='var(--primary-hover)'" onmouseout="this.style.background='var(--primary)'">
                        Save Profile Changes
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>

@section('scripts')
<script>
    function previewAvatar(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                const initials = document.getElementById('avatarInitials');
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (initials) {
                    initials.style.display = 'none';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
@endsection


