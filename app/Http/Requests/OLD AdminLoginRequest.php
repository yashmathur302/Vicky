<?php

namespace App\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginType = filter_var($this->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $loginType => $this->login_id,
            'password' => $this->password,
            'user_role' => 'admin',
        ];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            // DUAL HIT: Dono par strike lagao
            RateLimiter::hit($this->ipKey(), 60); // 1 min IP ban for testing
            RateLimiter::hit($this->emailKey(), 60); // 1 min Email ban for testing

            // User ko smaller remaining attempts dikhayenge
            $retriesLeft = min(
                RateLimiter::retriesLeft($this->ipKey(), 10),
                RateLimiter::retriesLeft($this->emailKey(), 5)
            );

            $errorMessage = trans('auth.failed');
            if ($retriesLeft > 0) {
                $errorMessage .= " You have {$retriesLeft} attempts left.";
            }

            throw ValidationException::withMessages([
                'login_id' => $errorMessage,
            ]);
        }

        // Login success par dono locks clear kardo
        RateLimiter::clear($this->ipKey());
        RateLimiter::clear($this->emailKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        // DUAL CHECK: Agar Email par 5 strikes ho gaye YA IP par 10 strikes ho gaye
        if (RateLimiter::tooManyAttempts($this->emailKey(), 4) || RateLimiter::tooManyAttempts($this->ipKey(), 4)) {
            
            event(new Lockout($this));

            // Jo bhi limit zyada der me khulegi, uske seconds nikal lo
            $seconds = max(
                RateLimiter::availableIn($this->emailKey()),
                RateLimiter::availableIn($this->ipKey())
            );

            throw new HttpResponseException(response()->json([
                'message' => 'Too many login attempts.',
                'locked_seconds' => $seconds
            ], 429));
        }
    }

    // NAYA: Do alag-alag keys banayi hain
    protected function emailKey(): string
    {
        return 'login-email|' . Str::lower($this->input('login_id'));
    }

    protected function ipKey(): string
    {
        return 'login-ip|' . $this->ip();
    }
}