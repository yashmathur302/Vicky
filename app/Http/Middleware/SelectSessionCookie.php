<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SelectSessionCookie
{
    /**
     * Keep the admin and frontend authentication sessions independent.
     *
     * This middleware must run before Laravel's StartSession middleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        config([
            'session.cookie' => $request->is('admin', 'admin/*')
                ? config('session.admin_cookie')
                : config('session.web_cookie'),
        ]);

        return $next($request);
    }
}
