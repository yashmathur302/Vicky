<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBannedUser
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('web');

        if ($guard->check() && $guard->user()->is_banned) {
            $guard->logout();
            // $request->session()->regenerateToken();   // NO invalidate()

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Your account has been banned.'], 403);
            }

            return redirect()->route('login')->with('error', 'Your account has been banned by the administration.');
        }

        // if (Auth::check() && Auth::user()->is_banned == 1) {
        // if (Auth::check() && Auth::user()->is_banned) {
            
        //     Auth::logout();
        //     $request->session()->invalidate();
        //     $request->session()->regenerateToken();

        //     // Agar AJAX request hai toh JSON return karega, warna login page par bhej dega
        //     if ($request->expectsJson() || $request->ajax()) {
        //         return response()->json(['message' => 'Your account has been banned.'], 403);
        //     }

        //     return redirect()->route('login')->with('error', 'Your account has been banned by the administration.');
        // }

        return $next($request);
    }
}