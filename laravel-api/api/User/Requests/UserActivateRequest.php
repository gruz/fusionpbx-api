<?php

namespace Api\User\Requests;

use Infrastructure\Http\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class UserActivateRequest extends FormRequest
{
    use ApiRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'hash' => [
                'required',
                'uuid',
                'exists:\Api\User\Models\User,user_enabled',

            ],
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['hash'] = $this->route('hash');
        return $data;
    }
}
