<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailReminder extends VerifyEmail
{
    protected function buildMailMessage($url)
    {
        $timeLimit = config('verification.ban_after_minutes') - config('verification.reminder_after_minutes');

        return (new MailMessage)
            ->subject('Action required: verify your email')
            ->greeting('Hi there!')
            ->line("You signed up recently but haven't verified your email address yet.")
            ->line("If your email is not verified within {$timeLimit} minutes, your account will be suspended. Please verify your email address to avoid this.")
            // ->action('Verify Email Address', $url)
            ->line('If you did not create this account, you can ignore this email.');
    }
}