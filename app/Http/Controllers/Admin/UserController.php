<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserInfo;
use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Carbon\carbon;
use App\Support\SmartLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyUserEmail;
use App\Mail\EmailVerifiedConfirmation;
use App\Mail\UserBannedMail;

class UserController extends Controller
{
    public function getList(Request $request)
    {
        // LISTING & FILTERING
        $query = User::with('userInfo')
            ->where('user_role', '!=', 'admin')
            ->orderBy('created_at', 'desc');
            
        if ($request->filled('search_name')) {
            $query->where('name', 'like', $request->search_name . '%');
        }

        $users = $query->paginate(15);

        return view('admin.users.list', compact('users'));
    }
    
    // public function getList(Request $request)
    // {
    //     // $timeLimit = now()->subMinutes(2);
    //     $timeLimit = now()->subDays(1);

    //     // 1. ACTIVE se INACTIVE karna (Time Limit Cross ya Soft Delete)
    //     User::withTrashed() // Deleted users ko bhi query me shamil karein
    //         ->where('account_status', 'active')
    //         ->where('user_role', '!=', 'admin') // Admin hamesha safe hai
    //         ->where(function ($query) use ($timeLimit) {
                
    //             // Case A: Last login purana ho gaya hai
    //             $query->where('last_login', '<', $timeLimit)
                
    //                   // Case B: Naya account tha par grace period nikal gaya
    //                   ->orWhere(function ($subQ) use ($timeLimit) {
    //                       $subQ->whereNull('last_login')
    //                            ->where('created_at', '<', $timeLimit); 
    //                   })
                      
    //                   // EDGE CASE FIX: Agar user soft delete ho chuka hai, toh usko inactive kardo
    //                   ->orWhereNotNull('deleted_at');
    //         })
    //         ->update(['account_status' => 'inactive']);


    //     // LISTING & FILTERING
    //     $query = User::with('userInfo')
    //         ->where('user_role', '!=', 'admin')
    //         ->orderBy('created_at', 'desc');
            
    //     if ($request->filled('search_name')) {
    //         $query->where('name', 'like', $request->search_name . '%');
    //     }

    //     $users = $query->paginate(15);

    //     return view('admin.users.list', compact('users'));
    // }

    public function getBannedList(Request $request)
    {
        $query = User::with('userInfo')
            ->where('user_role', '!=', 'admin')
            ->where('is_banned', 1) // Sirf banned users aayenge
            ->orderBy('created_at', 'desc');
            
        // Same optimized filters
        if ($request->filled('search_name')) {
            $query->where('name', 'like', $request->search_name . '%');
        }
        if ($request->filled('search_username')) {
            $query->where('username', 'like', $request->search_username . '%');
        }

        $users = $query->paginate(15);

        // Nayi blade file return karein
        return view('admin.users.banned_user', compact('users'));
    }

    // Create Page (Form) Get Karega
    public function getCreate(Request $request)
    {
        return view('admin.users.create');
    }

    // Naya User Database me Save Karega (AJAX via Modal)
    public function postCreate(Request $request)
    {
        // Validation Logic
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'username' => [ 'required', 'string', 'min:3','max:20', 'regex:/^[a-zA-Z0-9_]+$/',  Rule::unique('users')->whereNull('deleted_at') ],
            'email'    => [
                'required', 'string', 'max:255', 'email:rfc,strict',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) {
                    $cleanEmail = strtolower(trim($value)); // Edge case fix
                    $exists = User::where('email_hash', blind_hash($cleanEmail))
                                ->whereNull('deleted_at')
                                ->exists();
                    if ($exists) {
                        $fail('The email has already been taken.');
                    }
                }
            ],
            'password' => [ 'required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols(), 'regex:/^[A-Z]\S*$/' ],
        ]);

        // Validation fail hone par JSON error response 
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            // Check karte hain ki kya ye email ya username kisi deleted user ka hai
            $cleanEmail = strtolower(trim($request->email));
            
            $user = User::withTrashed()
                ->where(function($query) use ($request, $cleanEmail) {
                    // Email ki jagah email_hash check hoga
                    $query->where('email_hash', blind_hash($cleanEmail)) 
                          ->orWhere('username', $request->username);
                })->first();

            // Agar user mila aur wo deleted (trashed) hai, to restore karenge
            if ($user && $user->trashed()) {
                // $user->account_status = 'active';
                $user->restore(); // deleted_at ko wapas null kar dega
            } else {
                // Agar nahi mila to normally naya object banayenge
                $user = new User();
            }

            // Create Core User
            // $user = new User();
            $user->name      = $request->name;
            $user->email = strtolower(trim($request->email));
            // $user->email     = $request->email;
            $user->username  = $request->username;
            $user->password  = Hash::make($request->password);
            $user->user_role = 'user';
            $user->save();

            DB::commit();

            $this->sendVerificationEmail($user);

            // Success message
            session()->flash('success', 'User created successfully.');
            return response()->json(['message' => 'User created successfully.', 'status' => 'success']);

        } catch (\Throwable $th) {
            DB::rollBack();
            // Log::error('User Creation Error: ' . $th->getMessage());
            SmartLog::throttledError('User Creation Error: ' . $th->getMessage(), [], 60);
            return response()->json(['message' => 'Failed to create user. Please try again.'], 500);
        }
    }

    // Update Page (Form) Get Karega with existing user data
    public function getUpdate(Request $request, $id = null)
    {
        $user = User::with('userInfo')->findOrFail($id ?? $request->id);
        $loginHistories = LoginHistory::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(5); // Showing 5 rows per page
        return view('admin.users.update', compact('user', 'loginHistories'));
    }

    public function postUpdate(Request $request, $id)
    {
        // 1. Validation Logic
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'username' => "required|string|max:255|unique:users,username,{$id}",
            'email'    => [ 
                'required', 'string', 'max:255', 'email:rfc,strict',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                function ($attribute, $value, $fail) use ($id) {
                    $cleanEmail = strtolower(trim($value)); // Edge case fix
                    $exists = User::where('email_hash', blind_hash($cleanEmail))
                                ->where('id', '!=', $id) // Ignore current user
                                ->whereNull('deleted_at')
                                ->exists();
                    if ($exists) {
                        $fail('The email has already been taken.');
                    }
                }
            ],
            
            'profile_picture'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'bio'              => 'nullable|string',
            'gender'           => 'nullable|in:male,female,other',
            'dob'              => 'nullable|date_format:d/m/Y',
            'location'         => 'nullable|string|max:255',
            'website'          => 'nullable|url',
            'work'             => 'nullable|string|max:255',
            'facebook_link'    => 'nullable|url',
            'instagram_link'   => 'nullable|url',
            'linkedin_link'    => 'nullable|url',
            'twitter_link'     => 'nullable|url',
            'interests'        => 'nullable|string',
            'education'        => 'nullable|string',
            // 'account_status'   => 'nullable|in:active,inactive,banned',
            
            'password' => [ 
                'nullable', 'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
                'regex:/^\S*$/',
            ],
        ], [
            'password.regex' => 'The password should have a capital letter, a small letter, a special character and must not contain any spaces.'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            // A. Update Core User
            $user = User::findOrFail($id);
            $user->name         = $request->name;
            $user->email        = strtolower(trim($request->email));
            $user->username  = $request->username;
            
            // $user->email     = $request->email;
            // if ($request->has('email_verified_at')) {
            //     if (!$user->email_verified_at) {
            //         $user->email_verified_at = now();
            //     }
            // } else {
            //     $user->email_verified_at = null; 
            // }

            $isNewlyVerified = false; // Track karenge ki kya abhi verify hua hai

            if ($request->has('email_verified_at')) {
                // Agar DB me pehle null tha (unverified), aur ab toggle ON kiya hai
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    $isNewlyVerified = true; // Email bhejne ke liye confirmation
                }
            } else {
                $user->email_verified_at = null; 
            }

            if ($request->filled('password')) { 
                $user->password = Hash::make($request->password);
            }
            
            // $user->is_banned    = $request->has('is_banned') ? 1 : 0;

            // Check status BEFORE saving
            $wasBanned = $user->is_banned;
            $isNowBanned = $request->has('is_banned') ? 1 : 0;

            $user->is_banned = $isNowBanned;

            $user->save();

            // B. Update UserInfo Profile
            $userInfo = UserInfo::firstOrNew(['user_id' => $user->id]);
            $userInfo->bio            = $request->bio;
            $userInfo->gender         = $request->gender;
            $userInfo->location       = $request->location;
            $userInfo->website        = $request->website;
            $userInfo->work           = $request->work;
            $userInfo->facebook_link  = $request->facebook_link;
            $userInfo->instagram_link = $request->instagram_link;
            $userInfo->linkedin_link  = $request->linkedin_link;
            $userInfo->twitter_link   = $request->twitter_link;
            $userInfo->is_private           = $request->has('is_private') ? true : false;
            $userInfo->interests            = $request->filled('interests') ? array_map('trim', explode(',', $request->interests)) : null;
            $userInfo->education            = $request->filled('education') ? array_map('trim', explode(',', $request->education)) : null;
            
            if ($request->filled('dob')) {
                $dob = Carbon::createFromFormat('d/m/Y', $request->dob);
                if ($dob->greaterThan(now()->subYears(12))) {
                    return response()->json(['message' => 'User must be at least 12 years old to have an account.'], 422);
                }
                if ($dob->year < 1950) {
                    return response()->json(['message' => 'Date of birth cannot be earlier than 1950.'], 422);
                }
                $userInfo->dob = $dob->format('Y-m-d');
            } else {
                $userInfo->dob = null;
            }

            // Handle Image Removals
            // 1. Profile Picture
            // if ($request->remove_profile_picture == 1) {
            //     $oldFile = public_path("uploads/users/profile/profile_" . $user->id . ".webp");
            //     if (file_exists($oldFile)) @unlink($oldFile);
            // }

            // if ($request->hasFile('profile_picture')) {
            //     $profileImgName = 'profile_' . $user->id . '.webp';
            //     uploadFile('profile_picture', 400, 400, 'users/profile', null, null, $profileImgName);
            //     $userInfo->profile_picture = null; // DB null hi rahega
            // }

            // // 2. Cover Image
            // if ($request->remove_cover_image == 1) {
            //     $oldFile = public_path("uploads/users/cover/cover_" . $user->id . ".webp");
            //     if (file_exists($oldFile)) @unlink($oldFile);
            // }

            // if ($request->hasFile('cover_image')) {
            //     // FORCE THE NAME TO: cover_{user_id}.webp
            //     $coverImgName = 'cover_' . $user->id . '.webp';
            //     uploadFile('cover_image', 1200, 400, 'users/cover', null, null, $coverImgName);
            //     $userInfo->cover_image = null; 
            // }

            // Handle Image Removals
            
            // 1. Profile Picture
            if ($request->remove_profile_picture == 1) {
                $oldProfile = public_path("uploads/users/profile/profile_" . $user->id . ".webp");
                if (file_exists($oldProfile)) @unlink($oldProfile);
            }

            if ($request->hasFile('profile_picture')) {
                $profileImgName = 'profile_' . $user->id . '.webp';
                
                // Edge Case: Hard delete old file before saving new one to prevent permission lock
                $oldProfile = public_path("uploads/users/profile/" . $profileImgName);
                if (file_exists($oldProfile)) @unlink($oldProfile); 
                
                uploadFile('profile_picture', 400, 400, 'users/profile', null, null, $profileImgName);
                $userInfo->profile_picture = null; // DB null hi rahega
            }

            // 2. Cover Image
            if ($request->remove_cover_image == 1) {
                $oldCover = public_path("uploads/users/cover/cover_" . $user->id . ".webp");
                if (file_exists($oldCover)) @unlink($oldCover);
            }

            if ($request->hasFile('cover_image')) {
                $coverImgName = 'cover_' . $user->id . '.webp';
                
                // Edge Case: Hard delete old file before saving new one
                $oldCover = public_path("uploads/users/cover/" . $coverImgName);
                if (file_exists($oldCover)) @unlink($oldCover); 
                
                uploadFile('cover_image', 1200, 400, 'users/cover', null, null, $coverImgName);
                $userInfo->cover_image = null; // DB null hi rahega
            }

            $userInfo->save();

            DB::commit();

            if ($isNewlyVerified) {
                try {
                    Mail::to($user->email)->send(new EmailVerifiedConfirmation($user));
                } catch (\Exception $e) {
                    SmartLog::throttledError('Admin Verification Confirmation Mail Failed: ' . $e->getMessage(), [], 60);
                    // Agar mail fail bhi ho jaye, toh admin panel ka update process crash nahi hoga
                }
            }

            // SEND BAN NOTIFICATION EMAIL
            if (!$wasBanned && $isNowBanned) {
                try {
                    Mail::to($user->email)->send(new UserBannedMail($user));
                } catch (\Exception $e) {
                    // Fail silently so it doesn't break the admin's update process
                    SmartLog::throttledError('User Ban Notification Mail Failed: ' . $e->getMessage(), [], 60);
                }
            }

            session()->flash('success', 'User updated successfully.');

            return response()->json([
                'message' => 'Data updated successfully.',
                'redirect' => route('admin.users.list')
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            // Log::error('User Update Error: ' . $th->getMessage());
            SmartLog::throttledError('User Update Error: ' . $th->getMessage(), [], 60);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 500);
        }
    }
    
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            // throw new \Exception("Database connection lost intentionally for testing.");
            $user = User::with('userInfo')->findOrFail($id);

            // Agar user ki profile info exist karti hai
            if ($user->userInfo) {
                
                // Profile Picture delete
                if (!empty($user->userInfo->profile_picture) && $user->userInfo->profile_picture !== 'default.jpg') {
                    $profilePath = public_path("uploads/users/profile/" . $user->userInfo->profile_picture);
                    if (file_exists($profilePath)) {
                        @unlink($profilePath); // @ errors ko suppress karta hai agar file OS level par lock ho
                    }
                }

                // Cover Image delete
                if (!empty($user->userInfo->cover_image) && $user->userInfo->cover_image !== 'default.jpg') {
                    $coverPath = public_path("uploads/users/cover/" . $user->userInfo->cover_image);
                    if (file_exists($coverPath)) {
                        @unlink($coverPath);
                    }
                }

                // C. UserInfo data permanently delete
                $user->userInfo()->delete();
            }

            LoginHistory::where('user_id', $user->id)->delete();
            $user->account_status = 'inactive';
            $user->email_verified_at =  null;
            $user->save();

            // Main user delete
            $user->delete();

            DB::commit();
            return redirect()->back()->with('success', 'User deleted successfully.');

        } catch (\Exception $e) {

            DB::rollBack();
            // Log::error('User Deletion Failed: ' . $e->getMessage());
            SmartLog::throttledError('User Deletion Failed: ' . $e->getMessage(), [], 60);

            return redirect()->back()->with('error', 'Failed to delete user. Please try again.');
        }
    }

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

            if (Auth::check() && Auth::user()->user_role === 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'This user email is already verified.');
            }
            
            // Agar koi aur dusra user logged in hai, toh use logout karo (Security)
            if (Auth::check() && Auth::id() != $user->id) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // if (Auth::check()) {
            //     Auth::logout();
            //     $request->session()->invalidate();
            //     $request->session()->regenerateToken();
            // }

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

        if (Auth::check()) {
            if (Auth::id() == $user->id) {
                // Jo user verify hua hai, wahi logged in hai
                return redirect()->route('home')->with('success', 'Email verified successfully! You can now continue.');
            } elseif (Auth::user()->user_role === 'admin') {
                // Admin test kar raha hai, usey wapas admin panel bhejo
                return redirect()->route('admin.dashboard')->with('success', 'User email verified successfully!');
            } else {
                // Case C: Koi randomly dusra user logged in hai, use logout karke login par bhejo
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('success', 'Email verified successfully! Please log in to your account.');
            }
        }

        // 7. Final Smart Redirect
        $redirectRoute = Auth::check() ? 'home' : 'login';
        return redirect()->route($redirectRoute)->with('success', 'Email verified successfully! You can now continue.');
    }
}