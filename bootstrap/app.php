<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/frontend.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // AuthenticateSession removed on purpose (see notes below)

        $middleware->alias([
            'check.admin'  => \App\Http\Middleware\CheckAdminStatus::class,
            'check.user'   => \App\Http\Middleware\CheckUserStatus::class,
            'check.banned' => \App\Http\Middleware\CheckBannedUser::class,
        ]);

        // Where to send a NOT-logged-in visitor
        $middleware->redirectGuestsTo(fn (Request $request) =>
            $request->is('admin', 'admin/*')
                ? route('admin.auth.login')
                : route('login')
        );

        // Where to send an ALREADY-logged-in visitor who opens a login page
        $middleware->redirectUsersTo(fn (Request $request) =>
            $request->is('admin', 'admin/*')
                ? route('admin.dashboard')
                : route('home')
        );

        $middleware->append(\App\Http\Middleware\RunVerificationReminders::class);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {

            // Stale logout page: just log that side out
            if ($request->is('admin/logout')) {
                Auth::guard('admin')->logout();
                return redirect()->route('admin.auth.login');
            }
            if ($request->is('logout')) {
                Auth::guard('web')->logout();
                return redirect()->route('login');
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Page expired. Please reload and try again.'], 419);
            }

            return redirect()->route(
                $request->is('admin', 'admin/*') ? 'admin.auth.login' : 'login'
            )->with('error', 'Your page expired. Please try again.');
        });
    })
    
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();