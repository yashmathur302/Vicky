<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SmartLog
{
    /**
     * Log an error only once within the specified time frame.
     *
     * @param string $message The error message.
     * @param array $context Additional data to log.
     * @param int $seconds How long to suppress identical errors (default 60s).
     */
    public static function throttledError(string $message, array $context = [], int $seconds = 60): void
    {
        // Unique fingerprint create karein error message ke base par
        $key = 'log_throttle_' . md5($message);

        // Cache::add sirf tabhi true dega agar ye specific error pichle 60 seconds me nahi aaya
        if (Cache::add($key, true, $seconds)) {
            Log::error($message, $context);
        }
    }
}