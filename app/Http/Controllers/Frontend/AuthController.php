<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Models\UserInfo;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;
use App\Support\SmartLog;
use Illuminate\Support\Facades\Log;
use App\Support\ClientInfo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\VerifyUserEmail;
use App\Mail\EmailVerifiedConfirmation;
use App\Http\Requests\FrontendForgotPasswordRequest;
use \App\Mail\FrontendPasswordResetMail;
use Illuminate\Support\Str;
use App\Http\Requests\FrontendResetPasswordRequest;
use App\Http\Requests\FrontendLoginRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Support\Facades\Schema;
use App\Mail\PasswordResetSuccessMail;


class AuthController extends Controller
{
    private function getRealIp(Request $request): ?string
    {
        $requestIp = $request->ip();

        if (in_array($requestIp, ['127.0.0.1', '::1'], true)) {
            // OPTIMIZATION: Local IP ko 1 ghante ke liye cache kara hai taaki har login par 3 sec lag na aaye
            return Cache::remember('local_public_ip', 3600, function () {
                try {
                    $response = Http::timeout(3)->get('https://api.ipify.org');
                    if ($response->successful() && filter_var(trim($response->body()), FILTER_VALIDATE_IP)) {
                        return trim($response->body());
                    }
                } catch (\Throwable $e) {}
                return '127.0.0.1'; // Fallback
            });
        }

        return $requestIp;
    }

    private function updateLoginTracking(Request $request, User $user): void
    {
        try {
            $clientInfo = ClientInfo::extract($request);
            $currentIp = $this->getRealIp($request);

            $user->ip_address = $currentIp;
            $user->browser = $clientInfo['browser'];
            $user->operating_system = $clientInfo['operating_system'];
            $user->last_login = now();
            $user->session = $request->session()->getId();
            $user->save();

            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $currentIp,
                'browser' => $clientInfo['browser'],
                'operating_system' => $clientInfo['operating_system'],
            ]);
        } catch (\Throwable $e) {
            // EDGE CASE: Agar tracking fail ho jaye, toh user ka login crash nahi hona chahiye
            SmartLog::throttledError('Login Tracking Failed: ' . $e->getMessage(), [], 60);
        }
    }

    public function getRegister()
    {
        return view('frontend.auth.register');
    }

    public function postRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'username' => [ 'required', 'string', 'min:3','max:20', 'regex:/^[a-zA-Z0-9_]+$/', Rule::unique('users')->whereNull('deleted_at') ],
            'email'    => [
                'required', 'email', 'max:255', 'email:rfc,strict',
                function ($attribute, $value, $fail) {
                    if (User::where('email_hash', blind_hash($value))->exists()) {
                        $fail('The email has already been taken.');
                    }
                }
            ],
            'password' => ['required', 'confirmed', Password::min(8)->max(20)->mixedCase()->numbers()->symbols()],
            'terms'    => ['accepted'],
        ], [
            // Custom friendly message
            'terms.accepted' => 'You must read and accept the Terms & Conditions to register.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            // Check karte hain ki kya ye email ya username kisi deleted user ka hai
            $cleanEmail = strtolower(trim($request->email));
            
            $user = User::withTrashed()
                ->where(function($query) use ($request, $cleanEmail) {
                    // email ki jagah email_hash check hoga
                    $query->where('email_hash', blind_hash($cleanEmail)) 
                          ->orWhere('username', $request->username);
                })->first();
            // Agar user mila aur wo deleted (trashed) hai, to restore karenge
            if ($user && $user->trashed()) {
                // $user->account_status = 'active';
                $user->restore();
            } else {
                $user = new User();
            }
            $user->name             = $request->name;
            $user->email            = Str::lower($request->email);
            $user->email_hash       = blind_hash(Str::lower($request->email));    
            $user->username         = $request->username;
            $user->password         = Hash::make($request->password);
            $user->user_role        = 'user';
            $user->save(); 

            DB::commit();

            // 3. Auto Login & Session Regen
            Auth::login($user);
            $request->session()->regenerate();
            $this->updateLoginTracking($request, $user);

            //Verification Email after Registration
            $this->sendVerificationEmail($user);

            return response()->json([
                'message'  => 'Registration successful! Please check your email to verify your account.',
                'redirect' => route('home')
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            SmartLog::throttledError('Frontend Registration Error: ' . $th->getMessage(), [], 60);
            return response()->json(['message' => 'Failed to register. Please try again.'], 500);
        }
    }

    public function getLogin()
    {
        return response()
            ->view('frontend.auth.login')
            ->header(
                'Accept-CH',
                'Sec-CH-UA-Full-Version-List, Sec-CH-UA-Platform-Version'
            );
    }

    public function postLogin(FrontendLoginRequest $request)
    {
        $user = $request->authenticate();
        Auth::guard('web')->login($user, $request->boolean('remember'));
        $this->updateLoginTracking($request, $user);
        return response()->json([
            'message'  => 'Authentication successful. Redirecting...',
            'redirect' => route('home')
        ]);
    }

    public function postLogout(Request $request)
    {
        Auth::guard('web')->logout();
        // $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // EMAIL VERIFICATION LOGIC
    private function sendVerificationEmail(User $user)
    {
        // NOTE: Abhi testing ke liye 1 minute hai. Production me ise increase kar denge!
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(1),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        try {
            Mail::to($user->email)->send(new VerifyUserEmail($user, $verificationUrl));
        } catch (\Exception $e) {
            SmartLog::throttledError('Verification Mail Failed to Send: ' . $e->getMessage(), [], 60);
        }
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        // 1. Time-Limit & URL Signature Check
        if (!$request->hasValidSignature()) {
            return redirect()->route('login')->with('error', 'Your email verification link has expired or is invalid.');
        }

        $user = User::findOrFail($id);

        // 2. Banned User Edge Case
        if ($user->is_banned) {
            // Fix: Sirf tabhi logout karein jab wahi banned user logged in ho. Admin ko logout na karein.
            if (Auth::check() && Auth::id() == $user->id) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            return redirect()->route('login')->with('error', 'Your account is banned. Verification denied.');
        }

        // 3. Tamper Protection
        if (!hash_equals((string) $hash, sha1($user->email))) {
            return redirect()->route('login')->with('error', 'Invalid verification link.');
        }

        // 4. One-Click / Single Use Condition
        if ($user->email_verified_at) {
            // ADMIN EDGE CASE FIX
            if (Auth::check()) {
                if (Auth::user()->user_role === 'admin') {
                    return redirect()->route('admin.dashboard')->with('success', 'This user email is already verified.');
                }
                if (Auth::id() != $user->id) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }
            return redirect()->route('login')->with('error', 'This verification link has already been used and is now expired.');
        }

        // 5. Success: DB Update
        $user->email_verified_at = now();
        $user->save();

        // 6. Send Confirmation Email
        try {
            Mail::to($user->email)->send(new EmailVerifiedConfirmation($user));
        } catch (\Exception $e) {
            SmartLog::throttledError('Verification Confirmation Mail Failed: ' . $e->getMessage(), [], 60);
        }

        // 7. Final Smart Redirect (Admin Protection included)
        if (Auth::check()) {
            if (Auth::id() == $user->id) {
                return redirect()->route('home')->with('success', 'Email verified successfully! You can now continue.');
            } elseif (Auth::user()->user_role === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'User email verified successfully!');
            } else {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('success', 'Email verified successfully! Please log in to your account.');
            }
        }

        return redirect()->route('login')->with('success', 'Email verified successfully! You can now continue.');
    }

    //Resend the verification email (Rate Limited via Route)
    public function resendVerificationEmail(Request $request)
    {
        $user = Auth::user();
        
        if ($user->email_verified_at) {
            return response()->json(['message' => 'Your email is already verified.'], 400);
        }

        // SPAM PROTECTION: 60 Seconds Cooldown Limit
        $cacheKey = 'resend_verification_cooldown_' . $user->id;
        if (Cache::has($cacheKey)) {
            $secondsRemaining = Cache::get($cacheKey) - now()->timestamp;
            if ($secondsRemaining > 0) {
                return response()->json(['message' => "Please wait {$secondsRemaining} seconds before requesting another email."], 429);
            }
        }

        try {
            $this->sendVerificationEmail($user);
            
            // Mail jane ke baad 60 seconds ka lock laga dein
            Cache::put($cacheKey, now()->addSeconds(60)->timestamp, 60);
            
            return response()->json(['message' => 'Verification link sent to your email.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send verification email. Please try again later.'], 500);
        }
    }

    // FORGOT PASSWORD FLOW
    public function getForgotPassword()
    {
        return view('frontend.auth.forgot-password');
    }

    public function postForgotPassword(FrontendForgotPasswordRequest $request)
    {
        // 1. Rate Limiting Check
        $request->ensureIsNotRateLimited();

        // 2. Target User Lookup
        $user = User::where('email_hash', blind_hash($request->email))
                    ->where('user_role', 'user')
                    ->first();

        if ($user) {
            // Check for Hard Lock on account
            $hardLockKey = 'frontend-hard-lock|' . $user->id;
            if (Cache::has($hardLockKey)) {
                $lockExpiryTimestamp = Cache::get($hardLockKey);
                $secondsRemaining = $lockExpiryTimestamp - now()->timestamp;

                if ($secondsRemaining > 0) {
                    return response()->json([
                        'message'        => 'Your account is locked due to multiple failed logins.',
                        'locked_seconds' => $secondsRemaining
                    ], 429);
                } else {
                    Cache::forget($hardLockKey);
                }
            }

            // Generate Random Token
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token'      => Hash::make($token),
                    'created_at' => Carbon::now()
                ]
            );

            // PREPARE URLs OUTSIDE THE MAILABLE
            // 1. Password Reset URL
            $resetUrl = route('frontend.password.reset.index', [
                'token' => $token,
                'email' => $request->email
            ]);

            // 2. Report Unauthorized URL (Signed - 48 Hours)
            $reportUrl = null;
            try {
                $reportUrl = URL::temporarySignedRoute(
                    'account.report',
                    now()->addMinutes(5),
                    [
                        'user' => $user->id,
                        'hash' => sha1($user->email)
                    ]
                );
            } catch (\Throwable $routeError) {
                Log::error('Report URL Generation Error: ' . $routeError->getMessage(), [
                    'file' => $routeError->getFile(),
                    'line' => $routeError->getLine()
                ]);
            }


            // 3. Send Email
            try {
                Mail::to($request->email)->send(
                    new FrontendPasswordResetMail($user->name, $request->email, $resetUrl, $reportUrl)
                );
            } catch (\Throwable $th) {
                Log::error('Password Reset Mail Dispatch Failed: ' . $th->getMessage(), [
                    'email' => $request->email,
                    'file'  => $th->getFile(),
                    'line'  => $th->getLine()
                ]);
            }
        }

        // 4. Register Rate Limit Hit
        $request->hitRateLimiter();

        return response()->json([
            'message'  => "If this email is correct, you'll get the reset link.",
            'redirect' => route('login')
        ]);
    }

    // RESET PASSWORD FLOW
    public function getResetPassword(Request $request, string $token)
    {
        $email = strtolower(trim($request->email));
        $tokenData = DB::table('password_reset_tokens')->where('email', $email)->first();

        // Token expiry & Tamper protection
        if (!$tokenData || !Hash::check($token, $tokenData->token) || Carbon::parse($tokenData->created_at)->addMinutes(2)->isPast()) {
            return redirect()->route('frontend.forgot.password.index')->with('error', 'The reset link has expired or is invalid. Request a new one.');
        }

        $cacheKey = 'reset-click|' . $email;
        
        if (Cache::has($cacheKey)) {
            $firstClickedAt = Cache::get($cacheKey);
            $secondsElapsed = now()->timestamp - $firstClickedAt;

            // Agar pehle click ko 60 seconds se zyada ho gaye hain
            if ($secondsElapsed > 60) {
                // Security: Token DB se permanently hamesha ke liye udha do
                DB::table('password_reset_tokens')->where('email', $email)->delete();
                Cache::forget($cacheKey);
                
                return redirect()->route('frontend.forgot.password.index')->with('error', 'Your password reset session expired 60 seconds after opening the link. Please request a new one.');
            }
        } else {
            // Pehli baar click kiya hai, toh current timestamp Cache me daal do (valid for 15 mins)
            Cache::put($cacheKey, now()->timestamp, 900); 
        }

        return view('frontend.auth.reset-password', compact('token', 'email'));
    }

    public function postResetPassword(FrontendResetPasswordRequest $request)
    {
        $cleanEmail = strtolower(trim($request->email));
        $resetRecord = DB::table('password_reset_tokens')->where('email', $cleanEmail)->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token) || Carbon::parse($resetRecord->created_at)->addMinutes(2)->isPast()) {
            return response()->json([
                'message' => 'Your session has expired. Please request a new link.',
                'redirect' => route('frontend.forgot.password.index') 
            ], 422);
        }

        $cacheKey = 'reset-click|' . $cleanEmail;
        
        if (Cache::has($cacheKey)) {
            $firstClickedAt = Cache::get($cacheKey);
            
            // Agar page open karke 60 seconds se zyada wait karke submit kiya
            if (now()->timestamp - $firstClickedAt > 60) {
                DB::table('password_reset_tokens')->where('email', $cleanEmail)->delete();
                Cache::forget($cacheKey);
                
                return response()->json([
                    'message' => 'Your session expired because you took longer than 60 seconds to submit the new password. Please request a new link.',
                    'redirect' => route('frontend.forgot.password.index') 
                ], 422);
            }
        }

        try {
            $user = User::where('email_hash', blind_hash($cleanEmail))->where('user_role', 'user')->first();
            
            if ($user) {
                // Edge Case 5: Cannot reuse the exact same password
                if (Hash::check($request->password, $user->password)) {
                    return response()->json(['message' => 'Your new password cannot be the same as your current password.'], 422);
                }
                
                $user->password = Hash::make($request->password);
                
                // ADD: MULTI-DEVICE SESSION LOGOUT LOGIC
                if ($request->boolean('logout_all_devices')) {
                    // Remember me tokens invalidate
                    $user->setRememberToken(Str::random(60));

                    // Database session destroy
                    if (Schema::hasTable('sessions')) {
                        DB::table('sessions')->where('user_id', $user->id)->delete();
                    }
                }

                $user->save();

                // CONFIRMATION EMAIL TO ADMIN
                try {
                    Mail::to($user->email)->send(new PasswordResetSuccessMail($user));
                } catch (\Throwable $e) {
                    SmartLog::throttledError('Frontend Password Reset Confirmation Mail Failed: ' . $e->getMessage(), [], 60);
                }

                // RateLimiter::clear('frontend-login-attempts|' . $user->id);
                // Cache::forget('frontend-lock-level|' . $user->id);
                // Cache::forget('frontend-hard-lock|' . $user->id);

                // Clear any security locks on successful reset (if progressive lockout is enabled on frontend)
                // \Illuminate\Support\Facades\RateLimiter::clear('frontend-login-attempts|' . $user->id);
            }
            
            // Single-use token. Delete immediately after success.
            DB::table('password_reset_tokens')->where('email', $cleanEmail)->delete();
            Cache::forget($cacheKey);

            return response()->json([
                'message' => 'Password reset successfully! Redirecting...',
                'redirect' => route('login')
            ]);

        } catch (\Exception $e) {
            Log::error('Frontend Password Reset Error: ' . $e->getMessage());
            return response()->json(['message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    // Show Report Form
    public function getReport(Request $request, $userId, $hash)
    {
        if (!$request->hasValidSignature()) {
            if (Auth::check()) {
                return redirect()->route('home')->with('error', 'The report link has expired. Please visit the security section to report this issue.');
            }
            return redirect()->route('login')->with('error', 'The report link has expired or is invalid.');
        }

        $user = User::findOrFail($userId);

        if (!hash_equals((string) $hash, sha1($user->email))) {
            if (Auth::check()) {
                return redirect()->route('home')->with('error', 'Invalid report link parameters.');
            }
            return redirect()->route('login')->with('error', 'Invalid report link parameters.');
        }
        return view('frontend.auth.report', [
            'user' => $user,
            'hash' => $hash
        ]);
    }

    public function postReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|exists:users,id',
            'hash'        => 'required|string',
            'description' => 'required|string|min:10|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = User::findOrFail($request->user_id);

        if (!hash_equals((string) $request->hash, sha1($user->email))) {
            return response()->json(['message' => 'Unauthorized submission.'], 403);
        }

        // DYNAMIC REDIRECT: Check if user is already logged in
        $redirectUrl = Auth::check() ? route('home') : route('login');

        // Spam prevention check
        $alreadyReported = DB::table('reports')
            ->where('reporter_user_id', $user->id)
            ->where('reported_user_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if ($alreadyReported) {
            return response()->json([
                'message'  => 'Your report is already under review. Our security team will inspect it.',
                'redirect' => $redirectUrl
            ]);
        }

        // Insert into reports table
        DB::table('reports')->insert([
            'reporter_user_id' => $user->id,
            'reported_user_id' => $user->id,
            'reason'           => $request->description,
            'status'           => 'pending',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Security action: Target user ke sabhi pending reset tokens ko delete kar dein
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return response()->json([
            'message'  => 'Security alert reported successfully. Pending password reset links have been cancelled.',
            'redirect' => $redirectUrl
        ]);
    }
}