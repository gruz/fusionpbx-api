<?php

namespace Infrastructure\Auth\Requests;

use Infrastructure\Http\ApiRequest;

class LoginRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username'    => 'required',
            'domain_name'    => 'required',
            'password' => 'required'
        ];
    }
}
