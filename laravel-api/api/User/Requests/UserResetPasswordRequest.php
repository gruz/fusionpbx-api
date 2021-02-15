<?php

namespace Api\User\Requests;

use Infrastructure\Http\ApiRequest;

class UserResetPasswordRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'token' => 'required',
        ];
    }

}
