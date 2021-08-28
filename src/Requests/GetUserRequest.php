<?php

namespace Gruz\FPBX\Requests;

use Gruz\FPBX\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class GetUserRequest extends FormRequest
{
    use ApiRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'user_uuid' => [
                'required',
                'uuid',
            ],
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['user_uuid'] = $this->route('user_uuid');

        return $data;
    }
}
