<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('admin');

        if ($guard->check()) {
            $user = $guard->user();

            if (!in_array($user->user_role, ['admin', 'superadmin', 'subadmin'])) {
                return $this->terminateSession($request, 'Unauthorized access.');
            }

            if ($user->userInfo) {
                $status = strtolower($user->userInfo->account_status);
                if (in_array($status, ['banned', 'inactive', 'suspended'])) {
                    return $this->terminateSession($request, 'Your account has been ' . $status . '. Please contact administration.');
                }
            }

            if (method_exists($user, 'trashed') && $user->trashed()) {
                return $this->terminateSession($request, 'Your account no longer exists.');
            }
        }

        return $next($request);
    }

    private function terminateSession(Request $request, string $message)
    {
        Auth::guard('admin')->logout();
        // $request->session()->regenerateToken();   // NO invalidate()

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('admin.auth.login')->with('error', $message);
    }
}