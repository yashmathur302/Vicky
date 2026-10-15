@component('mail::layout')



{{-- For Empty Header (app name removed) --}}

@slot('header')

@endslot



{{-- MAIN CONTENT --}}

# Password Reset Request



Hello **{{ $name }}**,



We received a request to reset your password for your account associated with **{{ $email }}**.



@component('mail::button', ['url' => route('admin.password.reset.index', ['token' => $token, 'email' => $email])])

Reset My Password

@endcomponent



<span style="color: red;">⚠️ Important:</span> This link will expire in 15 minutes.



Thanks,<br>

{{ config('app.name') }}



@slot('footer')

@component('mail::footer')

    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.

@endcomponent

@endslot



@endcomponent