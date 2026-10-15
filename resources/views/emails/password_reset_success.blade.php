@component('mail::message')
# Password Changed Successfully 🔒

Hello **{{ $user->name }}**,

Your password for your account associated with **{{ $user->email }}** has been successfully changed.

If you did not make this change, please contact our support team immediately to secure your account.

Thanks,<br>
{{ config('app.name') }} Security Team
@endcomponent