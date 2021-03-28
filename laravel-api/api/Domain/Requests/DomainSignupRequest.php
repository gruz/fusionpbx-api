<?php

namespace Api\Domain\Requests;

use Infrastructure\Http\ApiRequest;
use Infrastructure\Rules\HostnameRule;
use Infrastructure\Rules\ArrayAtLeastOneAcceptedRule;

class DomainSignupRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'domain_name' => [
                'required',
                'unique:Api\\Domain\\Models\\Domain,domain_name'
            ],
            'users' => 'required',
            // 'is_subdomain' => 'required',
            'users.*.username' => 'required|distinct',
            'users.*.user_email' => 'required|distinct:ignore_case|email',
            'users.*.password' => 'required|min:6|max:25',
            'users.*.extensions.*.extension' => 'required|distinct|integer|min:1|max:999',
            'users.*.extensions.*.password' => 'required|min:6|max:25',
            'users.*.extensions.*.voicemail_password' => 'required|integer',
            'users' => new ArrayAtLeastOneAcceptedRule('is_admin'),
        ];

        if (!$this->request->get('is_subdomain')) {
            $rules['domain_name'][] = new HostnameRule();
        }

        return $rules;
    }
}
