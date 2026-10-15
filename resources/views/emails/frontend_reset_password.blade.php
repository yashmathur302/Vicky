@component('mail::message')
# Password Reset Request

Hello **{{ $name }}**,

We received a request to reset your password for your account associated with **{{ $email }}**.

@component('mail::button', ['url' => $resetUrl])
Reset My Password
@endcomponent

<span style="color: red;">⚠️ Important:</span> This link will expire shortly.

@if(!empty($reportUrl))
---

### Didn't request this?
If you did not request a password reset, someone else may be attempting to access your account. You can report this unauthorized activity:

@component('mail::button', ['url' => $reportUrl, 'color' => 'error'])
Report Unauthorized Request
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }} Security Team
@endcomponent