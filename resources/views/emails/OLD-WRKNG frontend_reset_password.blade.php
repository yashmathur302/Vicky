@component('mail::layout')

@slot('header')
@endslot

# Password Reset Request

Hello **{{ $name }}**,

We received a request to reset your password for your account associated with **{{ $email }}**.

@component('mail::button', ['url' => route('frontend.password.reset.index', ['token' => $token, 'email' => $email])])
Reset My Password
@endcomponent

<span style="color: red;">⚠️ Important:</span> This link will expire in 1 minute. If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}

@slot('footer')
@component('mail::footer')
    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
@endcomponent
@endslot

@endcomponent