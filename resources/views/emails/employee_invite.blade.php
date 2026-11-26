@component('mail::message')
# Hello {{ $user->name ?? 'there' }},

@if(!empty($messageText))
{!! nl2br(e($messageText)) !!}
@else
You have been invited to join {{ $companyName }}. Click the button below to set your password and activate your account. This link is valid for 7 days and can be used once.
@endif

@component('mail::button', ['url' => $inviteLink])
Accept Invite & Set Up Account
@endcomponent

If the button above does not work, copy and paste this URL into your browser:
{{ $inviteLink }}

Thanks,<br>
{{ $companyName }} Team
@endcomponent
