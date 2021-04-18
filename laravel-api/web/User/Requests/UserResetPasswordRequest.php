<?php

namespace Web\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Infrastructure\Rules\ResetPasswordTokenValidRule;

class UserResetPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'token' => [
                'required',
                new ResetPasswordTokenValidRule(request()->email, request()->domain_name, request()->token),
            ],
            // 'domain_name' => 'required|exists:password_resets',
            'email' => 'required|email|exists:password_resets,email,domain_name,' . request()->domain_name,
        ];
    }

    public function messages()
    {
        return [
            'domain_name.exists' => __('Invalid data'),
            'email.exists' => __('Invalid data'),
        ];
    }

    public function getRedirectUrl() {
        return route('password.invalid-link');
    }
}
