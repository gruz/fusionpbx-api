<?php

namespace Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserResetPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    // public $redirect = '/';

    public function rules()
    {
        return [
            'token' => 'required',
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
}
