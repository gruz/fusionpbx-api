<?php

namespace Api\User\Requests;

use Infrastructure\Http\ApiRequest;

class UserForgotPasswordRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'domain_name'    => 'required',
            'user_email' => 'required|email'
        ];
    }
   
}
