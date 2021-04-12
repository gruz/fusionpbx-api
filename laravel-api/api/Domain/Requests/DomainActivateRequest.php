<?php

namespace Api\Domain\Requests;

use Infrastructure\Http\ApiRequest;
use Infrastructure\Rules\DomainSignupHashExpiredRule;
use Infrastructure\Rules\DomainSignupHashHasEmailExistsRule;

class DomainActivateRequest extends ApiRequest
{
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
                'exists:\Api\PostponedAction\Models\PostponedAction',
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
