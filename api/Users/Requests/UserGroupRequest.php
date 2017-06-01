<?php

namespace Api\Users\Requests;

use App\Http\ApiRequest;

class UserGroupsRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'groups' => 'array|required'
        ];
    }
}
