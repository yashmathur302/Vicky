<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\User;
use App\Mail\FrontendAccountLockedMail;
use App\Mail\UserBannedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class FrontendLoginRequest extends FormRequest
{
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

    // Ye function validation ke baad user return karega taaki controller me session track ho sake
    public function authenticate(): User
    {
        $loginId = $this->login_id;
        $isEmail = filter_var($loginId, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            $user = User::where('email_hash', blind_hash($loginId))->where('user_role', 'user')->first();
        } else {
            // $user = User::where('username_hash', blind_hash($loginId))->where('user_role', 'user')->first();
            $user = User::where('username', $loginId)->where('user_role', 'user')->first();
        }

        // Logic 1: Invalid Email / Non-User 
        if (! $user) {
            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed'),
            ]);
        }

        // AUTO BAN INTERCEPTION AT LOGIN (If time expired)
        if (!$user->email_verified_at && $user->is_banned == 0) {
            $mins = $user->created_at->diffInMinutes(now());
            
            if ($mins >= 2) { // Testing ke liye 2, baad me ise 7 * 24 * 60 (10080) kar dena
                $user->is_banned = 1;
                $user->save();
                try {
                    Mail::to($user->email)->send(new UserBannedMail($user));
                } catch (\Exception $e) {}
                
                // Note: Ab iska 'is_banned' 1 ho chuka hai, toh ye automatically niche wale Logic 2 me fass jayega!
            }
        }

        // Logic 2: Banned User Check 
        if ($user->is_banned) {
            throw new HttpResponseException(response()->json([
                'message' => 'Your account has been banned by the administration. You cannot access this platform.'
            ], 403));
        }

        $attemptKey = 'frontend-login-attempts|' . $user->id;
        $lockLevelKey = 'frontend-lock-level|' . $user->id;
        $hardLockKey = 'frontend-hard-lock|' . $user->id;

        // Logic 3: Check Hard Lock Status
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

        // Logic 4: Check Password
        if (! Hash::check($this->password, $user->password)) {
            
            RateLimiter::hit($attemptKey, 600); 
            $retriesLeft = RateLimiter::retriesLeft($attemptKey, 5);

            if ($retriesLeft <= 0) {
                
                $currentLevel = Cache::get($lockLevelKey, 0);
                $newLevel = min($currentLevel + 1, 5); 
                
                Cache::put($lockLevelKey, $newLevel, 600 * 2);

                $lockTimes = [
                    1 => 60,        // 1 Min
                    2 => 120,       // 15 Mins
                    3 => 180,      // 1 Hour
                    4 => 240,     // 6 Hours
                    5 => 300,     // 24 Hours
                ];
                $lockDuration = $lockTimes[$newLevel];

                Cache::put($hardLockKey, now()->addSeconds($lockDuration)->timestamp, $lockDuration);

                // ==========================================
                // PREPARE URLs FOR FRONTEND USER
                // ==========================================
                // User ke case me reset button nahi dena hai, isliye null
                $resetUrl = null; 
                $reportUrl = null;

                try {
                    $reportUrl = URL::temporarySignedRoute(
                        'account.report',
                        now()->addMinutes(2),
                        [
                            'user' => $user->id,
                            'hash' => sha1($user->email)
                        ]
                    );
                } catch (\Throwable $routeError) {
                    Log::error('Lock Report URL Generation Error: ' . $routeError->getMessage());
                }

                // Send Alert Email
                try {
                    Mail::to($user->email)->send(new FrontendAccountLockedMail($user, $lockDuration, $resetUrl, $reportUrl));
                } catch (\Exception $e) {
                    // Fail silently for user experience
                    Log::error('Frontend Lockout Mail Failed: ' . $e->getMessage());
                }


                // Send Alert Email
                // try {
                //     Mail::to($user->email)->send(new FrontendAccountLockedMail($user, $lockDuration));
                // } catch (\Exception $e) {
                //     // Fail silently for user experience
                // }

                RateLimiter::clear($attemptKey);

                throw new HttpResponseException(response()->json([
                    'message' => 'Too many login attempts.',
                    'locked_seconds' => $lockDuration
                ], 429));
            }

            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed') . " You have {$retriesLeft} attempts left.",
            ]);
        }

        // Logic 5: Reset all trackers on Success
        RateLimiter::clear($attemptKey);
        Cache::forget($lockLevelKey);
        Cache::forget($hardLockKey);

        return $user;
    }
}