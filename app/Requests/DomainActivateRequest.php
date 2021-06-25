<?php

namespace App\Requests;

use App\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\DomainSignupHashExpiredRule;
use App\Rules\DomainSignupHashHasEmailExistsRule;

class DomainActivateRequest extends FormRequest
{
    use ApiRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $hash = $this->route('hash');

        $rules = [
            'hash' => [
                'bail',
                'required',
                'uuid',
                'exists:\App\Models\PostponedAction',
                new DomainSignupHashExpiredRule(),
            ],
            'email' => [
                'required',
                new DomainSignupHashHasEmailExistsRule($hash),
            ],
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['hash'] = $this->route('hash');
        $data['email'] = $this->route('email');
        return $data;
    }
}
