<?php

namespace Gruz\FPBX\Requests;

use Gruz\FPBX\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class CreateSettingRequest extends FormRequest
{
    use ApiRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // ~ 'extension' => 'array|required',
            // ~ 'extension.domain_uuid' => 'required|uuid'
            // ~ 'extension.extension' => 'required|integer|min:2|max:7'
        ];
    }

    public function attributes()
    {
        return [
            // ~ 'extension.domain_uuid' => 'domain uuid'
        ];
    }
}
