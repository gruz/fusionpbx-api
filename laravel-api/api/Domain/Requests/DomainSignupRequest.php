<?php

namespace Api\Domain\Requests;

use Illuminate\Validation\Rule;
use Infrastructure\Http\ApiRequest;
use Infrastructure\Rules\HostnameRule;
use Infrastructure\Rules\UsernameRule;
use Api\Settings\Models\DefaultSetting;
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
            'users' => 'required|array',
            // 'is_subdomain' => 'required',
            // 'users.*.username' => 'required|distinct|alpha_dash',
            'users.*.username' => [
                'required',
                'string',
                'max:255',
                new UsernameRule(),
            ],
            'users.*.user_email' => 'required|distinct:ignore_case|email',
            'users.*.password' => 'required|min:6|max:25',
            'users.*.extensions.*.extension' => 'required|distinct|integer|min:1|max:999',
            'users.*.extensions.*.password' => 'required|min:6|max:25',
            'users.*.extensions.*.voicemail_password' => 'required|integer',
            'users' => new ArrayAtLeastOneAcceptedRule('is_admin'),
        ];

        $resellerCodeRequired = config('fpbx.resellerCodeRequired');
        if ($resellerCodeRequired) {
            $resellecrCodeCheck = [
                Rule::exists(DefaultSetting::class, 'default_setting_value')
                    ->where('default_setting_category', 'billing')
                    ->where('default_setting_subcategory', 'reseller_code'),
            ];

            $rules['reseller_reference_code'] = array_merge(['required'], $resellecrCodeCheck);
            $rules['users.*.reseller_reference_code'] = $resellecrCodeCheck;
        }

        if (!$this->request->get('is_subdomain')) {
            $rules['domain_name'][] = new HostnameRule();
        }

        return $rules;
    }
}
