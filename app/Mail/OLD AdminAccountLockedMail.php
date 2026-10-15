<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminAccountLockedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $lockDurationInMinutes;

    // public function __construct($user, $lockDurationSeconds)
    // {
    //     $this->user = $user;
    //     // Seconds ko minutes/hours me convert karna for email readability
    //     $this->lockDurationInMinutes = $lockDurationSeconds >= 3600 
    //         ? ($lockDurationSeconds / 3600) . ' hours' 
    //         : ($lockDurationSeconds / 60) . ' minutes';
    // }

    public function __construct($user, $lockDurationInMinutes)
    {
        $this->user = $user;
        $this->lockDurationInMinutes = $lockDurationInMinutes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: Account Locked - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.account_locked',
        );
    }
}