<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FrontendAccountLockedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $lockDuration;
    public $resetUrl;
    public $reportUrl;

    // public function __construct($user, int $lockDurationSeconds)
    // {
    //     $this->user = $user;
    //     $this->lockDuration = $this->formatLockDuration($lockDurationSeconds);
    // }

    // public function __construct($user, int $lockDurationSeconds, string $resetUrl, ?string $reportUrl = null)
    // {
    //     $this->user = $user;
    //     $this->lockDuration = $this->formatLockDuration($lockDurationSeconds);
    //     $this->resetUrl = $resetUrl;
    //     $this->reportUrl = $reportUrl;
    // }

    public function __construct($user, int $lockDurationSeconds, ?string $resetUrl = null, ?string $reportUrl = null)
    {
        $this->user = $user;
        $this->lockDuration = $this->formatLockDuration($lockDurationSeconds);
        $this->resetUrl = $resetUrl;
        $this->reportUrl = $reportUrl;
    }

    private function formatLockDuration(int $seconds): string
    {
        if ($seconds >= 86400 && $seconds % 86400 === 0) {
            $value = intdiv($seconds, 86400);
            $unit = 'day';
        } elseif ($seconds >= 3600 && $seconds % 3600 === 0) {
            $value = intdiv($seconds, 3600);
            $unit = 'hour';
        } elseif ($seconds >= 60 && $seconds % 60 === 0) {
            $value = intdiv($seconds, 60);
            $unit = 'minute';
        } else {
            return $seconds . ' seconds';
        }

        return $value . ' ' . $unit . ($value === 1 ? '' : 's');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: Temporary Account Lock - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.account_locked',
        );
    }
}