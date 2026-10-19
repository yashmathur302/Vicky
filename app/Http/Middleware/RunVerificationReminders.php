<?php

namespace App\Http\Middleware;

use App\Services\VerificationReminderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RunVerificationReminders
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        // Testing ke liye har 1 minute me ek baar run hoga
        if (Cache::add('verification-reminders-last-run', true, now()->addMinutes(1))) {
            app(VerificationReminderService::class)->run();
        }
    }
}