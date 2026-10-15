<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAutoLogout
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            
            // Testing ke liye 60 seconds (Future me ise 600 yani 10 mins kar dena)
            $timeout = 60; 

            // Agar session me 'last_activity' set hai
            if (session()->has('last_activity')) {
                // Current time me se last activity time minus karo
                $idleTime = time() - session('last_activity');

                // Agar idle time timeout limit se zyada ho gaya hai
                if ($idleTime > $timeout) {
                    
                    // User ko securely logout karo
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    // Agar AJAX request (data load) ho rahi thi, toh JSON error bhejo
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'You have been logged out due to inactivity.'], 401);
                    }

                    // Normal page load par login page par redirect karo
                    return redirect()->route('admin.login.index')->with('error', 'You have been securely logged out due to inactivity.');
                }
            }

            // Har request par 'last_activity' ko current time se update kardo
            session(['last_activity' => time()]);
        }

        return $next($request);
    }
}