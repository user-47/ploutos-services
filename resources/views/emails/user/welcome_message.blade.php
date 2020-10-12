@component('mail::message')
# Welcome {{ $notifiable->first_name }},

Thank you for verifying your account on {{ config('app.name') }}. We welcome you to a smarter way to meet your international needs.

At {{ config('app.name') }}, we aim to make it easier for you to exchange currencies at your desired rates.

{{ config('app.name') }} opens a market place for you and I to come to an agreement.

### What next?:
1. [Complete your KYC]({{ config('setting.frontend.url') . config('setting.frontend.routes.home') }})
1. [Place a trade request]({{ config('setting.frontend.url') . config('setting.frontend.routes.home') }})
1. [Review open trade requests]({{ config('setting.frontend.url') . config('setting.frontend.routes.home') }})

Thanks,

{{ config('app.name') }} Team

@endcomponent