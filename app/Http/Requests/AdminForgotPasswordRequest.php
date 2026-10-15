<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class AdminForgotPasswordRequest extends FormRequest
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
        // Agar Email par 3 strikes ho gaye YA IP par 5 strikes ho gaye
        if (RateLimiter::tooManyAttempts($this->emailKey(), 3) || RateLimiter::tooManyAttempts($this->ipKey(), 5)) {
            
            // Jo bhi limit zyada der me khulegi, uske seconds nikal lo
            $seconds = max(
                RateLimiter::availableIn($this->emailKey()),
                RateLimiter::availableIn($this->ipKey())
            );

            throw new HttpResponseException(response()->json([
                'message' => 'Too many requests.',
                'locked_seconds' => $seconds
            ], 429));
        }
    }

    public function hitRateLimiter(): void
    {
        // Dono par strike lagao (60 seconds ka lock for testing, production me ise 300 yaani 5 mins kar denge)
        RateLimiter::hit($this->emailKey(), 60); 
        RateLimiter::hit($this->ipKey(), 60); 
    }

    
    protected function emailKey(): string
    {
        return 'forgot-email|' . Str::lower($this->input('email'));
    }

    protected function ipKey(): string
    {
        return 'forgot-ip|' . $this->ip();
    }
}