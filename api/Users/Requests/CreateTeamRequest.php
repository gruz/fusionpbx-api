<?php

namespace Api\Users\Requests;

use Infrastructure\Http\ApiRequest;

class CreateTeamRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'team' => 'array|required',
            'team.email' => 'required|email',
            'team.domain_name' => 'required|string',
            'team.password' => 'required|string|min:8',
            'team.username' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'team.email' => 'the team admin\'s email'
        ];
    }
}
