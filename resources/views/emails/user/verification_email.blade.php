@component('mail::message')
# Hello {{ $notifiable->first_name }},

Thank you for creating your account on {{ config('app.name') }}. You must verify your email address to activate your account. 

Please click the following button below within the next hour.

@component('mail::button', ['url' => $url])
Verify Email Address
@endcomponent

If you did not create an account, no further action is required.

Thanks,

{{ config('app.name') }} Team

@component('mail::subcopy')
If you’re having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser: {{ $url }} 
@endcomponent

@endcomponent