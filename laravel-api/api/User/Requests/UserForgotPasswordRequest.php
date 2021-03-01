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
            'domain_name'    => 'required|exists:v_domains,domain_name', 
            'user_email' => 'required|email|exists:v_users,user_email'
        ];
    }
   
}
