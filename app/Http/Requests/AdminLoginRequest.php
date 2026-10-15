<?php

namespace App\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use \App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminAccountLockedMail;
use App\Mail\FrontendAccountLockedMail;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\URL;

class AdminLoginRequest extends FormRequest
{
    private const ADMIN_ROLES = ['admin', 'superadmin', 'subadmin'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $loginId = $this->login_id;
        $isEmail = filter_var($loginId, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            $user = User::where('email_hash', blind_hash($loginId))
                ->whereIn('user_role', self::ADMIN_ROLES)->first();
        } else {
            $user = User::where('username', $loginId)
                ->whereIn('user_role', self::ADMIN_ROLES)->first();
        }

        // if ($isEmail) {
        //     $user = User::where('email_hash', blind_hash($loginId))->where('user_role', 'admin')->first();
        // } else {
        //     $user = User::where('username', $loginId)->where('user_role', 'admin')->first();
        // }

        // Logic 1: Invalid Email / Non-Admin (No attempts tracked)
        if (! $user) {
            throw ValidationException::withMessages([
                'login_id' => 'Invalid credentials or unauthorized access.',
            ]);
        }

        // Progressive Keys bound to specific User
        $attemptKey = 'login-attempts|' . $user->id;
        $lockLevelKey = 'lock-level|' . $user->id;
        $hardLockKey = 'hard-lock|' . $user->id;

        // 1. Check Hard Lock Status
        if (Cache::has($hardLockKey)) {
            $lockExpiryTimestamp = Cache::get($hardLockKey);
            $secondsRemaining = $lockExpiryTimestamp - now()->timestamp;
            
            if ($secondsRemaining > 0) {
                throw new HttpResponseException(response()->json([
                    'message' => 'Too many login attempts.',
                    'locked_seconds' => $secondsRemaining
                ], 429));
            } else {
                Cache::forget($hardLockKey);
            }
        }

        // 2. Check Password
        if (! Hash::check($this->password, $user->password)) {
            
            // 10 minutes ki window me 5 attempts allow karega
            RateLimiter::hit($attemptKey, 600); 
            $retriesLeft = RateLimiter::retriesLeft($attemptKey, 5);

            // Agar 5 attempts khatam ho gaye, tab lock lagao
            if ($retriesLeft <= 0) {
                
                // Fetch Current Lock Level
                $currentLevel = Cache::get($lockLevelKey, 0);
                $newLevel = min($currentLevel + 1, 5); // Maximum level 5 tak jayega
                
                // Level ko 48 ghante tak yaad rakho
                Cache::put($lockLevelKey, $newLevel, 600 * 2);

                // Define Progressive Timers (Seconds)
                $lockTimes = [
                    1 => 60,        // Level 1: 1 Min
                    2 => 120,       // Level 2: 15 Mins
                    3 => 180,      // Level 3: 1 Hour
                    4 => 240,     // Level 4: 6 Hours
                    5 => 300,     // Level 5: 24 Hours
                ];
                $lockDuration = $lockTimes[$newLevel];

                // Set Expiry Timestamp for the Frontend Timer
                Cache::put($hardLockKey, now()->addSeconds($lockDuration)->timestamp, $lockDuration);

                // PREPARE URLs FOR ADMIN USER
                $resetUrl = route('admin.forgot.password.index');
                $reportUrl = null; // Admin ke liye report button hide karna hai

                // Trigger Alert Email exactly once
                try {
                    Mail::to($user->email)->send(new FrontendAccountLockedMail($user, $lockDuration, $resetUrl, $reportUrl));
                } catch (\Exception $e) {
                    Log::error('Admin Lockout Mail Failed to send: ' . $e->getMessage(), [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
                // Trigger Alert Email exactly once
                // try {
                //     Mail::to($user->email)->send(new FrontendAccountLockedMail($user, $lockDuration));
                //     // Mail::to($user->email)->send(new AdminAccountLockedMail($user, $lockDuration));
                // } catch (\Exception $e) {
                //     Log::error('Admin Lockout Mail Failed to send: ' . $e->getMessage(), [
                //         'user_id' => $user->id,
                //         'email' => $user->email,
                //         'trace' => $e->getTraceAsString(),
                //     ]);
                // }

                // Agli baar ke liye (jab lock khulega) attempts fresh 5 kar do
                RateLimiter::clear($attemptKey);

                throw new HttpResponseException(response()->json([
                    'message' => 'Too many login attempts.',
                    'locked_seconds' => $lockDuration
                ], 429));
            }

            // Normal Incorrect Password (Attempts Left Message)
            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed') . " You have {$retriesLeft} attempts left.",
            ]);
        }

        // 3. Login Success
        // Auth::login($user, $this->boolean('remember'));
        Auth::guard('admin')->login($user, $this->boolean('remember'));
        
        // Success par saare negative trackers ko wipe kar do
        RateLimiter::clear($attemptKey);
        Cache::forget($lockLevelKey);
        Cache::forget($hardLockKey);
    }
}