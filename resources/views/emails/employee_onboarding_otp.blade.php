@component('mail::message')
# Verify Your Email Address — {{ $companyName }}

Use the one-time verification code below to confirm ownership of your email address on the **{{ $companyName }}** employee onboarding form:

@component('mail::panel')
<div style="font-size:28px;font-weight:700;letter-spacing:6px;text-align:center;">{{ $code }}</div>
@endcomponent

This verification code expires at **{{ $expiresAt->format('d M Y, h:i A T') }}** and can only be used for this onboarding session.

If you did not request this code, you can safely ignore this message.

Thanks,<br>
{{ $companyName }} HR Team
@endcomponent
