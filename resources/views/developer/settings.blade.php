@extends('layouts.developer')

@section('title', 'Settings')
@section('page_title', 'Developer Settings & Security')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px; max-width: 800px;">

    <!-- HEADER -->
    <div class="dev-card" style="margin-bottom: 0; padding: 26px;">
        <h1 style="font-size: 20px; font-weight: 800; color: var(--slate-dark);">Security &amp; Password Preferences</h1>
        <p style="font-size: 13.5px; color: var(--slate-muted); margin-top: 4px;">Update your developer credentials securely. Plaintext passwords are never stored or displayed.</p>
    </div>

    <!-- CHANGE PASSWORD CARD -->
    <div class="dev-card" style="padding: 28px;">
        <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-dark); margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
            Change Password
        </h3>

        <form method="POST" action="{{ route('developer.settings.password') }}" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf

            <div>
                <label style="font-size: 13px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 8px;">
                    Current Password:
                </label>
                <input type="password" name="current_password" required placeholder="••••••••" style="width: 100%; padding: 11px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13.5px; font-weight: 500; outline: none; box-shadow: var(--shadow-xs);">
            </div>

            <div>
                <label style="font-size: 13px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 8px;">
                    New Password:
                </label>
                <input type="password" name="new_password" required minlength="8" placeholder="Minimum 8 characters" style="width: 100%; padding: 11px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13.5px; font-weight: 500; outline: none; box-shadow: var(--shadow-xs);">
            </div>

            <div>
                <label style="font-size: 13px; font-weight: 700; color: var(--slate-dark); display: block; margin-bottom: 8px;">
                    Confirm New Password:
                </label>
                <input type="password" name="new_password_confirmation" required minlength="8" placeholder="Repeat new password" style="width: 100%; padding: 11px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13.5px; font-weight: 500; outline: none; box-shadow: var(--shadow-xs);">
            </div>

            <div style="padding-top: 10px; display: flex; justify-content: flex-end;">
                <button type="submit" style="padding: 11px 26px; border-radius: var(--radius-md); background: var(--slate-dark); color: #ffffff; border: none; font-size: 13.5px; font-weight: 700; cursor: pointer; box-shadow: var(--shadow-xs); transition: background 0.2s;" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='var(--slate-dark)'">
                    Update Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

