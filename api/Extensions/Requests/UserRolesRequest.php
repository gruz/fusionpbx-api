<?php

namespace Api\Users\Requests;

use Infrastructure\Http\ApiRequest;

class UserRolesRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'roles' => 'array|required'
        ];
    }
}
