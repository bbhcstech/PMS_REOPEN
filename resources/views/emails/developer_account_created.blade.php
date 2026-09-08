@component('mail::message')
# Hello {{ $user->name }},

Your developer account has been created successfully.

@component('mail::panel')
**Developer Dashboard:**  
[{{ $loginUrl }}]({{ $loginUrl }})

**Login Email:**  
`{{ $user->email }}`

**Temporary Password:**  
`{{ $tempPassword }}`
@endcomponent

Please log in and **change your password** after your first login to secure your account.

@component('mail::button', ['url' => $loginUrl, 'color' => 'success'])
Log In to Developer Dashboard
@endcomponent

Thanks,  
**{{ config('app.name') }} Team**
@endcomponent
