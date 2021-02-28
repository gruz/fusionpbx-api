<?php

namespace Api\User\Requests;

use Infrastructure\Http\ApiRequest;

class UserUpdatePasswordRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ];
    }
   
}
