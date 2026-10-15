@component('mail::layout')

{{-- For Empty Header (app name removed) --}}
@slot('header')
@endslot

{{-- MAIN CONTENT --}}
# Email Verified Successfully 🎉

Hello **{{ $user->name }}**,

Congratulations! Your email address has been successfully verified. 

You now have full access to all the features of your account on our platform. 

{{-- @component('mail::button', ['url' => route('login')])
Go to Login / Dashboard
@endcomponent --}}

Thanks,<br>
{{ config('app.name') }}

@slot('footer')
@component('mail::footer')
    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
@endcomponent
@endslot

@endcomponent