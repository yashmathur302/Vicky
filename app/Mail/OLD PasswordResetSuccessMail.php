<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PasswordResetSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $reportUrl;

    public function __construct($user)
    {
        $this->user = $user;

        // Sirf frontend user ke liye 1 min valid signed report URL generate hoga
        if ($user->user_role === 'user') {
            $this->reportUrl = URL::temporarySignedRoute(
                'account.report.unauthorized',
                now()->addMinutes(1),
                ['user' => $user->id, 'hash' => sha1($user->email)]
            );
        } else {
            $this->reportUrl = null;
        }
    }

    public function envelope(): Envelope
    {
        $subjectPrefix = in_array($this->user->user_role, ['admin', 'superadmin', 'subadmin']) ? 'Admin Security Alert' : 'Security Alert';
        return new Envelope(
            subject: "{$subjectPrefix}: Password Changed Successfully - " . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.password_reset_success',
        );
    }
}