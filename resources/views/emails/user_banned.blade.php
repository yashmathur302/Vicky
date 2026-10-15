@component('mail::message')
# Account Suspended 🚫

Hello **{{ $user->name }}**,

We are writing to inform you that your account on {{ config('app.name') }} has been permanently banned by the administration due to a violation of our terms of service or community guidelines. 

You will no longer be able to log in or access your account.

If you believe this is a mistake, you can contact our support team.

Thanks,<br>
{{ config('app.name') }} Administration
@endcomponent