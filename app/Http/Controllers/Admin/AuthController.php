<?php



namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminForgotPasswordRequest;
use App\Http\Requests\AdminResetPasswordRequest;
use \App\Mail\AdminPasswordResetMail;
use App\Support\SmartLog;
use Illuminate\Support\Facades\Cache;
use App\Mail\PasswordResetSuccessMail;

class AuthController extends Controller

{

    //Show the login page

    public function getLogin()
    {
        return view('admin.auth.login');
    }

    public function postLogin(AdminLoginRequest $request)
    {
        $request->authenticate();
        // $request->session()->regenerate();
        return response()->json([
            'message'  => 'Authentication successful. Redirecting...',
            'redirect' => route('admin.dashboard')
        ]);
    }

    public function postLogout(Request $request)
    {
        Auth::guard('admin')->logout();
        // $request->session()->regenerateToken();
        return redirect()->route('admin.auth.login');
    }



    // Show the Forgot Password Page

    public function getForgotPassword()

    {

        return view('admin.auth.forgot-password');

    }



    public function postForgotPassword(AdminForgotPasswordRequest $request)
    {
        // Rate Limiting Check
        $request->ensureIsNotRateLimited();

        // Business Logic
        $user = User::where('email_hash', blind_hash($request->email))->where('user_role', 'admin')->first();
        
        // $user = User::where('email_hash', blind_hash($request->email))->first();
        // $user = User::where('email', $request->email)->where('user_role', 'admin')->first();

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
                Mail::to($request->email)->send(new AdminPasswordResetMail($token, $request->email, $user->name));
            } catch (\Throwable $th) {
                // Log::error('Mail Sending Failed: ' . $th->getMessage());
                SmartLog::throttledError('Mail Sending Failed: ' . $th->getMessage(), [], 60);
                
            }
        }

        // 4. Register Rate Limit Hit
        $request->hitRateLimiter();

        return response()->json([
            'message'  => "If this email is correct, you'll get the reset link.",
            'redirect' => route('admin.auth.login')
        ]);
    }



    //Show the Password Reset Form

    public function getResetPassword(Request $request, string $token)
    {
        $email = strtolower(trim($request->email));
        $tokenData = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$tokenData || !Hash::check($token, $tokenData->token) || Carbon::parse($tokenData->created_at)->addMinutes(2)->isPast()) {
            return redirect()->route('admin.forgot.password.index')->with('error', 'The password reset link has expired or is invalid. Please request a new one.');
        }

        // 2. FIRST CLICK 60-SECONDS EXPIRY LOGIC
        $cacheKey = 'admin-reset-click|' . $email;

        if (Cache::has($cacheKey)) {
            $firstClickedAt = Cache::get($cacheKey);
            $secondsElapsed = now()->timestamp - $firstClickedAt;

            // Agar first click ko 60 seconds se zyada ho chuke hain
            if ($secondsElapsed > 60) {
                DB::table('password_reset_tokens')->where('email', $email)->delete();
                Cache::forget($cacheKey);

                return redirect()->route('admin.forgot.password.index')->with('error', 'Your password reset session expired 60 seconds after opening the link. Please request a new one.');
            }
        } else {
            // First time link click hone par timestamp store karein (valid for 2 mins)
            Cache::put($cacheKey, now()->timestamp, 120);
        }
        // ==========================================

        return view('admin.auth.reset-password', compact('token', 'email'));
    }

    public function postResetPassword(AdminResetPasswordRequest $request)
    {
        $email = strtolower(trim($request->email));
        $resetRecord = DB::table('password_reset_tokens')->where('email', $email)->first();

        // 1. Default Expiry Check (2 Minutes)
        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token) || Carbon::parse($resetRecord->created_at)->addMinutes(2)->isPast()) {
            return response()->json([
                'message' => 'Your session has expired. Please request a new link.',
                'redirect' => route('admin.forgot.password.index')
            ], 422);
        }

        // 2. First Click 60-Seconds Check
        $cacheKey = 'admin-reset-click|' . $email;

        if (Cache::has($cacheKey)) {
            $firstClickedAt = Cache::get($cacheKey);

            if (now()->timestamp - $firstClickedAt > 60) {
                DB::table('password_reset_tokens')->where('email', $email)->delete();
                Cache::forget($cacheKey);

                return response()->json([
                    'message' => 'Your session expired because it took longer than 60 seconds to submit. Please request a new link.',
                    'redirect' => route('admin.forgot.password.index')
                ], 422);
            }
        }

        try {
            $user = User::where('email_hash', blind_hash($email))->first();

            if ($user) {
                // Prevent same password reuse
                if (Hash::check($request->password, $user->password)) {
                    return response()->json(['message' => 'Your new password cannot be exactly the same as your current password.'], 422);
                }

                $user->password = Hash::make($request->password);

                // NAYA: LOGOUT FROM ALL DEVICES LOGIC
                if ($request->boolean('logout_all_devices')) {
                    // 1. Remember Token naya generate karein taaki purane 'Remember Me' cookies invalidate ho jayein
                    $user->setRememberToken(Str::random(60));

                    // 2. Agar database sessions driver use ho raha hai, toh DB sessions delete karein
                    if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                        DB::table('sessions')->where('user_id', $user->id)->delete();
                    }
                }

                $user->save();

                try {
                    Mail::to($user->email)->send(new PasswordResetSuccessMail($user));
                } catch (\Throwable $e) {
                    SmartLog::throttledError('Admin Password Reset Confirmation Mail Failed: ' . $e->getMessage(), [], 60);
                }

                // Clear previous security lockouts on successful reset
                RateLimiter::clear('login-attempts|' . $user->id);
                Cache::forget('lock-level|' . $user->id);
                Cache::forget('hard-lock|' . $user->id);
            }

            // Cleanup token & click timestamp
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            Cache::forget($cacheKey);

            return response()->json([
                'message' => 'Password reset successfully! Redirecting...',
                'redirect' => route('admin.auth.login')
            ]);

        } catch (\Exception $e) {
            SmartLog::throttledError('Password Reset Error: ' . $e->getMessage(), [], 60);
            return response()->json(['message' => 'Something went wrong. Please try again.'], 500);
        }
    }

    // public function postResetPassword(AdminResetPasswordRequest $request)
    // {
    //     $email = strtolower(trim($request->email));
    //     $resetRecord = DB::table('password_reset_tokens')->where('email', $email)->first();

    //     if (!$resetRecord || !Hash::check($request->token, $resetRecord->token) || Carbon::parse($resetRecord->created_at)->addMinutes(2)->isPast()) {
    //         return response()->json([
    //             'message' => 'Your session has expired. Please request a new link.',
    //             'redirect' => route('admin.forgot.password.index')
    //         ], 422);
    //     }

    //     // 2. FORM SUBMIT PAR 60-SECONDS CHECK
    //     $cacheKey = 'admin-reset-click|' . $email;

    //     if (Cache::has($cacheKey)) {
    //         $firstClickedAt = Cache::get($cacheKey);

    //         if (now()->timestamp - $firstClickedAt > 60) {
    //             DB::table('password_reset_tokens')->where('email', $email)->delete();
    //             Cache::forget($cacheKey);

    //             return response()->json([
    //                 'message' => 'Your session expired because it took longer than 60 seconds to submit. Please request a new link.',
    //                 'redirect' => route('admin.forgot.password.index')
    //             ], 422);
    //         }
    //     }

    //     try {
    //         $user = User::where('email_hash', blind_hash($email))->first();

    //         if ($user) {
    //             // Prevent exact same password reuse
    //             if (Hash::check($request->password, $user->password)) {
    //                 return response()->json(['message' => 'Your new password cannot be exactly the same as your current password.'], 422);
    //             }

    //             $user->password = Hash::make($request->password);

    //             if ($request->boolean('logout_all_devices')) {
    //                 // 1. Remember Token naya generate karein taaki purane 'Remember Me' cookies invalidate ho jayein
    //                 $user->setRememberToken(Str::random(60));

    //                 // 2. Agar database sessions driver use ho raha hai, toh DB sessions delete karein
    //                 if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
    //                     DB::table('sessions')->where('user_id', $user->id)->delete();
    //                 }
    //             }

    //             $user->save();

    //             // Clear previous security lockouts on successful reset
    //             RateLimiter::clear('login-attempts|' . $user->id);
    //             Cache::forget('lock-level|' . $user->id);
    //             Cache::forget('hard-lock|' . $user->id);
    //         }

    //         // Single-use token and session cleanup
    //         DB::table('password_reset_tokens')->where('email', $email)->delete(); 
    //         Cache::forget($cacheKey);

    //         return response()->json([
    //             'message' => 'Password reset successfully! Redirecting...',
    //             'redirect' => route('admin.auth.login')
    //         ]);

    //     } catch (\Exception $e) {
    //         SmartLog::throttledError('Password Reset Error: ' . $e->getMessage(), [], 60);
    //         return response()->json(['message' => 'Something went wrong. Please try again.'], 500);
    //     }
    // }



    //Process the New Password Submission

    // public function postResetPassword(AdminResetPasswordRequest $request)

    // {

    //     // Validation automatically passed

    //     $resetRecord = DB::table('password_reset_tokens')->where('email', $request->email)->first();



    //     if (!$resetRecord || !Hash::check($request->token, $resetRecord->token) || Carbon::parse($resetRecord->created_at)->addMinutes(15)->isPast()) {

    //         return response()->json([

    //             'message' => 'Your session has expired. Please request a new link.',

    //             'redirect' => route('admin.forgot.password.index') 

    //         ], 422);

    //     }



    //     try {

    //         // $user = User::where('email', $request->email)->first();

    //         $user = User::where('email_hash', blind_hash($request->email))->first();

    //         if ($user) {

    //             if (Hash::check($request->password, $user->password)) {

    //                 return response()->json([ 'message' => 'Your new password cannot be exactly the same as your current password.'], 422);

    //             }

    //             $user->password = Hash::make($request->password);

    //             $user->save();

    //             RateLimiter::clear('login-attempts|' . $user->id);
    //             Cache::forget('lock-level|' . $user->id);
    //             Cache::forget('hard-lock|' . $user->id);

    //         }

    //         DB::table('password_reset_tokens')->where('email', $request->email)->delete();



    //         return response()->json([

    //             'message' => 'Password reset successfully! Redirecting...',

    //             'redirect' => route('admin.auth.login')

    //         ]);



    //     } catch (\Exception $e) {

    //         // Log::error('Password Reset Error: ' . $e->getMessage());

    //         SmartLog::throttledError('Password Reset Error: ' . $e->getMessage(), [], 60);

    //         return response()->json(['message' => 'Something went wrong. Please try again.'], 500);

    //     }

    // }

}