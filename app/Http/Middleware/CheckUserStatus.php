<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');

        if ($guard->check() && $guard->user()->user_role !== 'user') {
            $guard->logout();
            // $request->session()->regenerateToken();   // NO invalidate()
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        // if (Auth::check()) {
        //     if (Auth::user()->user_role !== 'user') {
        //         Auth::logout();
        //         $request->session()->invalidate();
        //         $request->session()->regenerateToken();
        //         return redirect()->route('login')->with('error', 'Unauthorized access.');
        //     }
        // }

        return $next($request);
    }
}