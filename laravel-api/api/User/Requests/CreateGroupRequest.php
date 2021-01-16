<?php

namespace Api\User\Requests;

use Infrastructure\Http\ApiRequest;

class CreateGroupRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'group' => 'array|required',
            'group.group_name' => 'required|string',
            'group.group_description' => 'required|string'
        ];
    }

    public function attributes()
    {
        return [
            'group.group_name' => 'the group\'s name',
            'group.group_description' => 'the group\'s description',
        ];
    }
}
