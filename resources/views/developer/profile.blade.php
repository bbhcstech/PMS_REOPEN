@extends('layouts.developer')

@section('title', 'My Profile')
@section('page_title', 'Developer Profile')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- TOP HEADER -->
    <div class="dev-card" style="margin-bottom: 0;">
        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; border: 3px solid var(--primary-border); overflow: hidden;">
                @if(!empty($dev->profile_image) && file_exists(public_path($dev->profile_image)))
                    <img src="{{ asset($dev->profile_image) }}" alt="{{ $dev->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    {{ strtoupper(substr($dev->name ?? 'Dev', 0, 2)) }}
                @endif
            </div>

            <div style="flex: 1; min-width: 240px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <h1 style="font-size: 22px; font-weight: 800; color: var(--slate-dark);">{{ $dev->name }}</h1>
                    <span style="background: #ecfdf5; color: #059669; font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 12px; border: 1px solid #a7f3d0;">
                        ● {{ ucfirst($empDetail?->status ?? 'Available') }}
                    </span>
                </div>
                <p style="font-size: 13.5px; color: var(--primary); font-weight: 700; margin-top: 2px;">
                    {{ ucfirst($dev->designation ?? 'Backend Developer') }}
                </p>
                <span style="font-size: 12px; color: var(--slate-muted); font-family: monospace; display: block; margin-top: 4px;">
                    ID: DEV-{{ str_pad($dev->id, 3, '0', STR_PAD_LEFT) }} &bull; Joined: {{ $dev->created_at ? $dev->created_at->format('M Y') : 'Aug 2026' }}
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN PROFILE GRID -->
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">

        <!-- LEFT COLUMN: READ-ONLY SYSTEM METADATA -->
        <div class="dev-card" style="margin-bottom: 0;">
            <h3 style="font-size: 15px; font-weight: 800; color: var(--slate-dark); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                Account & System Authority
            </h3>

            <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13px;">
                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">FULL NAME</span>
                    <strong style="color: var(--slate-dark);">{{ $dev->name }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">REGISTERED EMAIL</span>
                    <strong style="color: var(--slate-dark);">{{ $dev->email }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">DEVELOPER ROLE</span>
                    <strong style="color: var(--primary);">{{ ucfirst($dev->role ?? 'Developer') }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">DEVELOPER ID</span>
                    <strong style="font-family: monospace; color: var(--slate-dark);">DEV-{{ str_pad($dev->id, 3, '0', STR_PAD_LEFT) }}</strong>
                </div>

                <div>
                    <span style="color: var(--slate-muted); font-size: 11px; font-weight: 700; display: block; text-transform: uppercase;">ACCOUNT STATUS</span>
                    <strong style="color: #059669;">ACTIVE &amp; VERIFIED</strong>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: AUTHORIZED EDITABLE FIELDS -->
        <div class="dev-card" style="margin-bottom: 0;">
            <h3 style="font-size: 15px; font-weight: 800; color: var(--slate-dark); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                Edit Authorized Profile Details
            </h3>

            <form method="POST" action="{{ route('developer.profile.update') }}" style="display: flex; flex-direction: column; gap: 18px;">
                @csrf

                <div>
                    <label style="font-size: 12.5px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 6px;">
                        Current Status / Availability:
                    </label>
                    <select name="status" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px; font-weight: 600; background: #ffffff;">
                        <option value="Available" {{ (strtolower($empDetail?->status ?? '') === 'available' || empty($empDetail?->status)) ? 'selected' : '' }}>Available</option>
                        <option value="Busy" {{ strtolower($empDetail?->status ?? '') === 'busy' ? 'selected' : '' }}>Busy</option>
                        <option value="On Leave" {{ strtolower($empDetail?->status ?? '') === 'on_leave' || strtolower($empDetail?->status ?? '') === 'on leave' ? 'selected' : '' }}>On Leave</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 12.5px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 6px;">
                        Mobile Contact Number:
                    </label>
                    <input type="text" name="mobile" value="{{ old('mobile', $dev->mobile ?? $empDetail?->mobile) }}" placeholder="+91 98765 43210" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px;">
                </div>

                <div>
                    <label style="font-size: 12.5px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 6px;">
                        Skills &amp; Tech Stack (Comma separated):
                    </label>
                    <input type="text" name="skills" value="{{ old('skills', $empDetail?->skills ?? implode(', ', $skillsArray)) }}" placeholder="Laravel, PHP, MySQL, REST API, Git, Vue.js" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px;">
                    <span style="font-size: 11.5px; color: var(--slate-muted); margin-top: 4px; display: block;">Example: Laravel, PHP, MySQL, REST API, Git</span>
                </div>

                <div>
                    <label style="font-size: 12.5px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 6px;">
                        Professional Bio / Summary:
                    </label>
                    <textarea name="about" rows="4" style="width: 100%; padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px; font-family: inherit; line-height: 1.5;" placeholder="Backend developer specializing in scalable Laravel applications and REST APIs.">{{ old('about', $dev->about ?? $empDetail?->about) }}</textarea>
                </div>

                <div style="padding-top: 10px; display: flex; justify-content: flex-end;">
                    <button type="submit" style="padding: 10px 24px; border-radius: var(--radius-md); background: var(--primary); color: #ffffff; border: none; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: var(--shadow-sm);">
                        Save Profile Changes
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
