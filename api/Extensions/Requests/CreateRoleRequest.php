<?php

namespace Api\Users\Requests;

use Infrastructure\Http\ApiRequest;

class CreateRoleRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'role' => 'array|required',
            'role.name' => 'required|string'
        ];
    }

    public function attributes()
    {
        return [
            'role.name' => 'the role\'s name'
        ];
    }
}
