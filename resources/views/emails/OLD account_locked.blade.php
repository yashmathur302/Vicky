@component('mail::message')
# Security Alert: Account Locked 🔒

Hello **{{ $user->name }}**,

We detected multiple consecutive failed login attempts on your account. For your security, your account has been temporarily locked for **{{ $lockDurationInMinutes }}**.

If this was you, please wait for the lockout period to expire before trying again. 

<span style="color: red;">⚠️ If you did not make these attempts, your account might be under attack. We highly recommend resetting your password immediately.</span>

@component('mail::button', ['url' => route('admin.forgot.password.index')])
Reset Password
@endcomponent

Thanks,<br>
{{ config('app.name') }} Security Team
@endcomponent