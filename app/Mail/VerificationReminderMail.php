<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $timeLeft;

    public function __construct(User $user, $timeLeft)
    {
        $this->user = $user;
        $this->timeLeft = $timeLeft; // Testing me '1 minute', real me '24 hours' bhejenge
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Action Required: Verify Your Email or Risk Account Ban - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.verification_reminder',
        );
    }
}