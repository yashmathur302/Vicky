@component('mail::message')
# Verify Your Email Immediately ⚠️

Hello **{{ $user->name }}**,

We noticed that you haven't verified your email address yet. To keep our platform secure, we require all users to verify their accounts.

If you do not verify your email within the next **{{ $timeLeft }}**, your account will be automatically and permanently banned.


Thanks,<br>
{{ config('app.name') }} Security Team
@endcomponent