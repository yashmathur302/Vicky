<?php

namespace App\Extensions;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class SecureDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * FIX 1: Admin aur Web dono ka user_id properly fetch karna
     */
    protected function userId()
    {
        // Pehle check karo agar Admin logged in hai
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->id();
        }

        // Phir check karo agar normal User logged in hai
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->id();
        }

        return parent::userId();
    }

    /**
     * Encrypt request metadata at the point Laravel adds it to the payload.
     */
    protected function addRequestInformation(&$payload)
    {
        parent::addRequestInformation($payload);

        if ($payload['ip_address'] !== null) {
            $payload['ip_address'] = Crypt::encryptString($payload['ip_address']);
        }

        if ($payload['user_agent'] !== null) {
            $payload['user_agent'] = Crypt::encryptString($payload['user_agent']);
        }

        return $this;
    }
}
