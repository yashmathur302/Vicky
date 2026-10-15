@component('mail::message')
# Security Alert: Account Locked 🔒

Hello **{{ $user->name }}**,

We detected multiple consecutive failed login attempts on your account. For your security, your account has been temporarily locked for **{{ $lockDuration }}**.

If this was you, please wait for the lockout period to expire before trying again.

@if(!empty($resetUrl))
<span style="color: red;">⚠️ If you did not make these attempts, your account might be under attack. We highly recommend resetting your password immediately.</span>

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent
@endif

@if(!empty($reportUrl))
---

### Didn't try to log in?
If you did not make these login attempts, someone else may be trying to access your account. You can report this unauthorized activity below:

@component('mail::button', ['url' => $reportUrl, 'color' => 'error'])
Report Unauthorized Activity
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }} Security Team
@endcomponent


{{-- @component('mail::message')
# Security Alert: Account Locked 🔒

Hello **{{ $user->name }}**,

We detected multiple consecutive failed login attempts on your account. For your security, your account has been temporarily locked for **{{ $lockDuration }}**.

If this was you, please wait for the lockout period to expire before trying again.

<span style="color: red;">⚠️ If you did not make these attempts, your account might be under attack. We highly recommend resetting your password immediately.</span>

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

@if(!empty($reportUrl))
---

### Didn't try to log in?
If you did not make these login attempts, someone else may be trying to access your account. You can report this unauthorized activity below:

@component('mail::button', ['url' => $reportUrl, 'color' => 'error'])
Report Unauthorized Activity
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }} Security Team
@endcomponent --}}