<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Support\SmartLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationReminderMail;
use App\Mail\UserBannedMail;

class DashboardController extends Controller
{
    public function index()
    {
        $this->autoProcessUnverifiedUsers();

        return view('admin.dashboard.index');
    }

    private function autoProcessUnverifiedUsers()
    {
        // Server load bachane ke liye ye script har 1 minute me sirf ek baar run hogi
        if (Cache::has('last_unverified_sweeper_run')) {
            return; 
        }
        Cache::put('last_unverified_sweeper_run', true, now()->addMinutes(1));

        // Unverified aur Unbanned users (Dono Admin created aur Frontend registered) nikalo
        $users = User::whereNull('email_verified_at')
                     ->where('is_banned', 0)
                     ->get();

        foreach ($users as $user) {
            // Check kitna time ho gaya account banaye
            $minutesSinceCreation = $user->created_at->diffInMinutes(now());

            // ==========================================
            // TESTING TIMERS (Minutes)
            // ==========================================
            $banThreshold = 2;       // 2 minutes baad BAN
            $reminderThreshold = 1;  // 1 minute baad REMINDER

            // PRODUCTION TIMERS (Future ke liye)
            // $banThreshold = 7 * 24 * 60;    // 7 Days
            // $reminderThreshold = 6 * 24 * 60; // 6 Days

            // 1. BAN CONDITION
            if ($minutesSinceCreation >= $banThreshold) {
                $user->is_banned = 1;
                $user->save();

                try {
                    Mail::to($user->email)->send(new UserBannedMail($user));
                } catch (\Exception $e) {
                    SmartLog::throttledError('Auto Ban Mail Failed: ' . $e->getMessage(), [], 60);
                }
                continue; // Mail bhej diya, ab agle user par chalo
            }

            // 2. REMINDER CONDITION
            if ($minutesSinceCreation >= $reminderThreshold) {
                $cacheKey = 'reminder_sent_' . $user->id;

                // Check karo kya isko already reminder bhej chuke hain?
                if (!Cache::has($cacheKey)) {
                    try {
                        Mail::to($user->email)->send(new VerificationReminderMail($user, '1 minute'));
                        
                        // Ek baar mail chala gaya, toh Cache me save karlo taaki dobara na jaye
                        Cache::put($cacheKey, true, now()->addDays(30));
                    } catch (\Exception $e) {
                        SmartLog::throttledError('Reminder Mail Failed: ' . $e->getMessage(), [], 60);
                    }
                }
            }
        }
    }
}