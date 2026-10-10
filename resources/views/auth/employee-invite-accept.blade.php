<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accept Invitation{{ !empty($company?->name) ? ' - ' . $company->name : '' }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px;
               font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; background: linear-gradient(135deg, #EEF2FF, #F8FAFC); color: #0F172A; }
        .card { width: min(460px, 100%); background: #fff; border: 1px solid #E2E8F0; border-radius: 20px; padding: 32px 28px; box-shadow: 0 20px 45px rgba(15, 23, 42, .08); }
        .icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; background: #DBEAFE; color: #2F6BFF; font-size: 26px; }
        h1 { font-size: 1.4rem; margin: 0 0 6px; }
        p { margin: 0 0 18px; color: #475569; line-height: 1.5; font-size: .95rem; }
        label { display: block; font-weight: 600; font-size: .88rem; margin: 14px 0 6px; }
        input { width: 100%; padding: 11px 13px; border: 1px solid #CBD5E1; border-radius: 10px; font-size: .95rem; }
        input:focus { outline: 3px solid rgba(47, 107, 255, .25); border-color: #2F6BFF; }
        .readonly { background: #F1F5F9; color: #475569; }
        .hint { font-size: .78rem; color: #64748B; margin-top: 6px; }
        .errors { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; border-radius: 10px; padding: 10px 12px; font-size: .88rem; margin-bottom: 8px; }
        .errors ul { margin: 0; padding-left: 18px; }
        button { width: 100%; margin-top: 22px; padding: 12px; border: 0; border-radius: 12px; background: #2F6BFF; color: #fff; font-weight: 700; font-size: 1rem; cursor: pointer; }
        button:hover { background: #1E4FCC; }
        a { color: #2F6BFF; }
    </style>
</head>
<body>
<main class="card">
    @if($invalid)
        <div class="icon" aria-hidden="true">!</div>
        <h1>This invitation link is no longer valid</h1>
        <p>The link has expired or has already been used. Please ask your {{ $company?->name ?? 'company' }} administrator to send a new invitation, or <a href="{{ route('login') }}">sign in</a> if you already set up your account.</p>
    @else
        <div class="icon" aria-hidden="true">✉</div>
        <h1>Join {{ $company->name }}</h1>
        <p>Set your name and password to activate your account.</p>

        @if(isset($errors) && $errors->any())
            <div class="errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ $submitUrl }}">
            @csrf
            <label for="inviteEmailReadonly">Email</label>
            <input id="inviteEmailReadonly" type="email" class="readonly" value="{{ $user->email }}" readonly>

            <label for="inviteName">Full name</label>
            <input id="inviteName" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="191" autocomplete="name">

            <label for="invitePassword">Password</label>
            <input id="invitePassword" name="password" type="password" required autocomplete="new-password">
            <div class="hint">At least 8 characters with an uppercase letter, a lowercase letter, a number and a special character.</div>

            <label for="invitePasswordConfirm">Confirm password</label>
            <input id="invitePasswordConfirm" name="password_confirmation" type="password" required autocomplete="new-password">

            <button type="submit">Activate account</button>
        </form>
    @endif
</main>
</body>
</html>
