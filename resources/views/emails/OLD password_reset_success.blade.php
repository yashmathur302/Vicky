@component('mail::message')
# Password Changed Successfully 🔒

Hello **{{ $user->name }}**,

Your password for your account associated with **{{ $user->email }}** has been successfully changed.

@if($user->user_role === 'user')
<span style="color: red;">⚠️ If you did not make this change, your account may be compromised. Please secure your account immediately.</span>

@component('mail::button', ['url' => $reportUrl, 'color' => 'error'])
Report
@endcomponent
@else
If you did not perform this action, please contact your systems administrator immediately.
@endif

Thanks,<br>
{{ config('app.name') }} Security Team
@endcomponent