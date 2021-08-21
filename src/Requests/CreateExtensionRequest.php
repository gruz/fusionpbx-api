<?php

namespace Gruz\FPBX\Requests;

use Gruz\FPBX\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class CreateExtensionRequest extends FormRequest
{
    use ApiRequestTrait;

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