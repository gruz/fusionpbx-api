<?php

namespace Api\Domain\Requests;

use Infrastructure\Http\ApiRequest;
use Infrastructure\Rules\DomainSignupHashExpiredRule;
use Infrastructure\Rules\DomainSignupHashHasEmailExistsRule;

class DomainSignupVerificationLinkRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if (app()->runningUnitTests()) {
            $hash = $GLOBALS['test.request.hash'];
        } else {
            $hash = $this->route('hash');
        }

        $rules = [
            'hash' => [
                'required',
                'uuid',
                'exists:\Api\PostponedAction\Models\PostponedAction',
                new DomainSignupHashExpiredRule(),
                // 'exists:postponed_action',
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
        return $data;
    }
}
