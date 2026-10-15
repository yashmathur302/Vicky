<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FrontendPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $resetUrl;
    public $reportUrl;

    /**
     * Mailable sirf ready-made variables receive karega.
     */
    public function __construct(string $name, string $email, string $resetUrl, ?string $reportUrl = null)
    {
        $this->name      = $name;
        $this->email     = $email;
        $this->resetUrl  = $resetUrl;
        $this->reportUrl = $reportUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Request - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.frontend_reset_password',
        );
    }
}