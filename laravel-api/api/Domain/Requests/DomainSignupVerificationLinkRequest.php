<?php

namespace Api\Domain\Requests;

use Infrastructure\Http\ApiRequest;

class DomainSignupVerificationLinkRequest extends ApiRequest
{
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
                'exists:\Api\PostponedAction\Models\PostponedAction',
                // 'exists:postponed_action',
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
