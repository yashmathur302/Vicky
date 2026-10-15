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

class AuthController extends Controller
{
    private function getRealIp(Request $request): ?string
    {
        $requestIp = $request->ip();

        if (in_array($requestIp, ['127.0.0.1', '::1'], true)) {
            try {
                $response = Http::timeout(3)
                    ->get('https://api.ipify.org');

                if ($response->successful()) {
                    $publicIp = trim($response->body());

                    if (filter_var($publicIp, FILTER_VALIDATE_IP)) {
                        return $publicIp;
                    }
                }
            } catch (\Throwable $e) {
                // Fallback below
            }
        }

        return $requestIp;
    }

    // private function getRealIp(Request $request): ?string
    // {
    //     return $request->ip();
    // }

    private function updateLoginTracking(Request $request, User $user): void
    {
        $clientInfo = ClientInfo::extract($request);

        $currentIp = $this->getRealIp($request);
        $loginTime = now();

        $user->ip_address = $currentIp;
        $user->browser = $clientInfo['browser'];
        $user->operating_system = $clientInfo['operating_system'];
        $user->last_login = $loginTime;
        $user->session = $request->session()->getId();
        $user->save();

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $currentIp,
            'browser' => $clientInfo['browser'],
            'operating_system' => $clientInfo['operating_system'],
        ]);
    }

    public function getRegister()
    {
        return view('frontend.auth.register');
    }

    public function postRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'username' => [ 'required', 'string', 'max:255', Rule::unique('users')->whereNull('deleted_at') ],
            'email'    => [
                'required', 'email',
                function ($attribute, $value, $fail) {
                    if (User::where('email_hash', blind_hash($value))->exists()) {
                        $fail('The email has already been taken.');
                    }
                }
            ],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
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

            // Create Core User
            // $user = new User();

            // 1. Create Core User
            // $user = new User();
            $user->name             = $request->name;
            // $user->email            = $request->email;
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

            //Registration ke baad Verification Email send karo
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

    public function postLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $loginId = $request->login_id;
        $isEmail = filter_var($loginId, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            $user = User::where('email_hash', blind_hash($loginId))->where('user_role', 'user')->first();
        } else {
            $user = User::where('username_hash', blind_hash($loginId))->where('user_role', 'user')->first();
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => trans('auth.failed')], 401);
        }

        // User banned hai to login hi nahi karne diya jayga
        // if ($user->is_banned == 1) {
        if ($user->is_banned) {
            return response()->json(['message' => 'Your account has been banned by the administration. You cannot access this platform.'], 403);
        }

        // 1. Authenticate & Secure Session
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        
        $this->updateLoginTracking($request, $user);

        return response()->json([
            'message'  => 'Authentication successful. Redirecting...',
            'redirect' => route('home')
        ]);
    }

    public function postLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // EMAIL VERIFICATION LOGIC
    //Reusable method to generate signed URL and send email
    private function sendVerificationEmail(User $user)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(1),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 2. Send the actual email
        Mail::to($user->email)->send(new VerifyUserEmail($user, $verificationUrl));
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
            if (Auth::check()) {
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

            // Agar Admin testing kar raha hai, toh usey wapas admin dashboard bhejo
            // if (Auth::check() && Auth::user()->user_role === 'admin') {
            //     return redirect()->route('admin.dashboard')->with('success', 'This user email is already verified.');
            // }
            
            // // Agar koi aur dusra user logged in hai, toh use logout karo (Security)
            // if (Auth::check() && Auth::id() != $user->id) {
            //     Auth::logout();
            //     $request->session()->invalidate();
            //     $request->session()->regenerateToken();
            // }
            
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->with('error', 'This verification link has already been used and is now expired.');
        }

        // 5. Success: DB Update Karein
        $user->email_verified_at = now();
        $user->save();

        // 6. Send Confirmation Email (Try-Catch block for safety)
        try {
            Mail::to($user->email)->send(new EmailVerifiedConfirmation($user));
        } catch (\Exception $e) {
            SmartLog::throttledError('Verification Confirmation Mail Failed: ' . $e->getMessage(), [], 60);
            // SmartLog::throttledError('Verification Confirmation Mail Failed: ' . $e->getMessage());
        }

        // Final Smart Redirect (Handling Session Conflicts)
        // if (Auth::check()) {
        //     if (Auth::id() == $user->id) {
        //         // Jo user verify hua hai, wahi logged in hai
        //         return redirect()->route('home')->with('success', 'Email verified successfully! You can now continue.');
        //     } elseif (Auth::user()->user_role === 'admin') {
        //         // Admin test kar raha hai, usey wapas admin panel bhejo
        //         return redirect()->route('admin.dashboard')->with('success', 'User email verified successfully!');
        //     } else {
        //         // Case C: Koi randomly dusra user logged in hai, use logout karke login par bhejo
        //         Auth::logout();
        //         $request->session()->invalidate();
        //         $request->session()->regenerateToken();
        //         return redirect()->route('login')->with('success', 'Email verified successfully! Please log in to your account.');
        //     }
        // }

        // 7. Final Smart Redirect
        $redirectRoute = Auth::check() ? 'home' : 'login';
        return redirect()->route($redirectRoute)->with('success', 'Email verified successfully! You can now continue.');
    }

    //Resend the verification email (Rate Limited via Route)
    public function resendVerificationEmail(Request $request)
    {
        $user = Auth::user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Your email is already verified.'], 400);
        }

        $this->sendVerificationEmail($user);

        return response()->json(['message' => 'Verification link sent to your email.']);
    }

    // FORGOT PASSWORD FLOW
    public function getForgotPassword()
    {
        return view('frontend.auth.forgot-password');
    }

    public function postForgotPassword(FrontendForgotPasswordRequest $request)
    {
        // Rate Limiting Check
        $request->ensureIsNotRateLimited();

        // Business Logic
       $user = User::where('email_hash', blind_hash($request->email))->where('user_role', 'user')->first();
        
        // $user = User::where('email', $request->email)->where('user_role', 'admin')->first();
        // $user = User::where('email_hash', blind_hash($request->email))->first();

        if ($user) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token), 
                    'created_at' => Carbon::now()
                ]
            );

            try {
                Mail::to($request->email)->send(new FrontendPasswordResetMail($token, $request->email, $user->name));
            } catch (\Throwable $th) {
                // Log::error('Mail Sending Failed: ' . $th->getMessage());
                SmartLog::throttledError('Mail Sending Failed: ' . $th->getMessage(), [], 60);
                
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

        // Edge Case 4: Token expiry (15 mins) & Tamper protection
        if (!$tokenData || !Hash::check($token, $tokenData->token) || Carbon::parse($tokenData->created_at)->addMinutes(15)->isPast()) {
            return redirect()->route('frontend.forgot.password.index')->with('error', 'The reset link has expired or is invalid.');
        }

        return view('frontend.auth.reset-password', compact('token', 'email'));
    }

    public function postResetPassword(FrontendResetPasswordRequest $request)
    {
        $cleanEmail = strtolower(trim($request->email));
        $resetRecord = DB::table('password_reset_tokens')->where('email', $cleanEmail)->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token) || Carbon::parse($resetRecord->created_at)->addMinutes(15)->isPast()) {
            return response()->json([
                'message' => 'Your session has expired. Please request a new link.',
                'redirect' => route('frontend.forgot.password.index') 
            ], 422);
        }

        try {
            $user = User::where('email_hash', blind_hash($cleanEmail))->where('user_role', 'user')->first();
            
            if ($user) {
                // Edge Case 5: Cannot reuse the exact same password
                if (Hash::check($request->password, $user->password)) {
                    return response()->json(['message' => 'Your new password cannot be same as your current password.'], 422);
                }
                
                $user->password = Hash::make($request->password);
                $user->save();

                // Clear any security locks on successful reset (if progressive lockout is enabled on frontend)
                \Illuminate\Support\Facades\RateLimiter::clear('frontend-login-attempts|' . $user->id);
            }
            
            // Edge Case 6: Single-use token. Delete immediately after success.
            DB::table('password_reset_tokens')->where('email', $cleanEmail)->delete();

            return response()->json([
                'message' => 'Password reset successfully! Redirecting...',
                'redirect' => route('login')
            ]);

        } catch (\Exception $e) {
            Log::error('Frontend Password Reset Error: ' . $e->getMessage());
            return response()->json(['message' => 'Something went wrong. Please try again.'], 500);
        }
    }
}