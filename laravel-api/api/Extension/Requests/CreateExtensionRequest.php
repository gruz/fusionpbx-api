<?php

namespace Api\Extension\Requests;

use Infrastructure\Http\ApiRequest;

class CreateExtensionRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'extension' => 'required',
            'extension.extension' => 'required',
            'extension.password' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
            'extension.domain_uuid' => 'domain uuid'
        ];
    }
}
