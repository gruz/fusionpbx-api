<?php

namespace Gruz\FPBX\Requests;

use Gruz\FPBX\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class GetUuidRequest extends FormRequest
{
    use ApiRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'uuid' => [
                'required',
                'uuid',
            ],
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['uuid'] = $this->route('uuid');

        return $data;
    }
}
