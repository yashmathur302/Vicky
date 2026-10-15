@component('mail::layout')

@slot('header')
@endslot

{{-- MAIN CONTENT --}}
# Verify Your Email Address

Hello **{{ $user->name }}**,

Thank you for registering on our platform! Please confirm your email address by clicking the button below:

@component('mail::button', ['url' => $verificationUrl])
Verify Email Address
@endcomponent

<span style="color: red;">⚠️ Important:</span> This link will expire in 60 minutes.

If you did not create an account, no further action is required.

Thanks,<br>
{{ config('app.name') }}

@slot('footer')
@component('mail::footer')
    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
@endcomponent
@endslot

@endcomponent