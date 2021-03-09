<?php

namespace Api\User\Requests;

use Infrastructure\Http\ApiRequest;
use Api\User\Models\User;
use Api\Domain\Models\Domain;

class UserForgotPasswordRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'domain_name' => 'required|exists:' . Domain::class . ',domain_name', 
            'user_email' => 'required|email|exists:' . User::class . ',user_email'
        ];
    }
   
}
