<?php

namespace Api\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserResetPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|exists:password_resets',
            'token' => 'required',
            'domain_name' => 'required|exists:password_resets'
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
