<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class FrontendForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function ensureIsNotRateLimited(): void
    {
        // 3 strikes per email OR 5 strikes per IP
        if (RateLimiter::tooManyAttempts($this->emailKey(), 3) || RateLimiter::tooManyAttempts($this->ipKey(), 5)) {
            $seconds = max(RateLimiter::availableIn($this->emailKey()), RateLimiter::availableIn($this->ipKey()));
            throw new HttpResponseException(response()->json([
                'message' => 'Too many requests.',
                'locked_seconds' => $seconds
            ], 429));
        }
    }

    public function hitRateLimiter(): void
    {
        RateLimiter::hit($this->emailKey(), 60); 
        RateLimiter::hit($this->ipKey(), 60); 
    }

    protected function emailKey(): string
    {
        return 'frontend-forgot-email|' . Str::lower(trim($this->input('email')));
    }

    protected function ipKey(): string
    {
        return 'frontend-forgot-ip|' . $this->ip();
    }
}