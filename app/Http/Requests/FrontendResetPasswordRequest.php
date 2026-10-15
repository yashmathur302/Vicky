<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class FrontendResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string'],
            'password' => [ 
                'required', 
                'confirmed', 
                Password::min(8)->mixedCase()->numbers()->symbols(),
                'regex:/^\S*$/', 
            ],
        ];
    }
    
    public function messages()
    {
        return [
            'password.regex' => 'The password must not contain any spaces.'
        ];
    }
}