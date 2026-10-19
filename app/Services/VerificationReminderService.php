<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\VerifyEmailReminder;
use App\Mail\UserBannedMail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Support\SmartLog;

class VerificationReminderService
{
    public function run(int $batchSize = 30): array
    {
        // Lock taaki multiple requests ek sath same emails spam na karein
        $lock = Cache::lock('verification-reminders-running', 300);
        if (!$lock->get()) {
            return ['reminded' => 0, 'banned' => 0];
        }

        try {
            return [
                'reminded' => $this->sendReminders($batchSize),
                'banned'   => $this->banUnverified(),
            ];
        } finally {
            $lock->release();
        }
    }

    protected function unverifiedActiveUsers(): Builder
    {
        return User::whereNull('email_verified_at')
            ->where(function ($q) {
                $q->where('is_banned', 0)->orWhereNull('is_banned');
            });
    }

    protected function sendReminders(int $batchSize): int
    {
        $users = $this->unverifiedActiveUsers()
            ->whereNull('verification_reminder_sent_at')
            ->where('created_at', '<=', now()->subMinutes(config('verification.reminder_after_minutes')))
            ->limit($batchSize)
            ->get();

        $sent = 0;
        foreach ($users as $user) {
            try {
                $user->notify(new VerifyEmailReminder);
                $user->forceFill(['verification_reminder_sent_at' => now()])->save();
                $sent++;
            } catch (\Throwable $e) {
                SmartLog::throttledError("Reminder fail user {$user->id}: " . $e->getMessage(), [], 60);
            }
        }
        return $sent;
    }

    protected function banUnverified(): int
    {
        // Strictly calculate the gap
        $noticeMins = config('verification.ban_after_minutes') - config('verification.reminder_after_minutes');

        $users = $this->unverifiedActiveUsers()
            ->where('created_at', '<=', now()->subMinutes(config('verification.ban_after_minutes')))
            ->whereNotNull('verification_reminder_sent_at')
            ->where('verification_reminder_sent_at', '<=', now()->subMinutes($noticeMins))
            ->get();

        $bannedCount = 0;
        foreach ($users as $user) {
            $user->is_banned = 1;
            $user->save();
            
            try {
                Mail::to($user->email)->send(new UserBannedMail($user));
            } catch (\Throwable $e) {
                SmartLog::throttledError("Ban mail fail user {$user->id}: " . $e->getMessage(), [], 60);
            }
            $bannedCount++;
        }
        return $bannedCount;
    }
}