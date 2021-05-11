<?php

namespace Infrastructure\Auth\Requests;

use Api\User\Models\User;
use Api\Domain\Models\Domain;
use Illuminate\Validation\Rule;
use Infrastructure\Rules\UsernameRule;
use Api\Settings\Models\DefaultSetting;
use Illuminate\Foundation\Http\FormRequest;
use Infrastructure\Services\ValidationRulesService;

class UserSignupRequest extends FormRequest
{
    private $validationRulesService;

    public function __construct(ValidationRulesService $validationRulesService)
    {
        $this->validationRulesService = $validationRulesService;
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'domain_name' => [
                'required',
                Rule::exists(Domain::class, 'domain_name')
                ->where('domain_enabled', true),
            ],
            'user_email' => [
                'required',
                Rule::unique(User::class)->where(function ($query) {
                    // $domain_name = $this->request->get('domain_name');
                    $domain_name = request()->get('domain_name');

                    $domain = Domain::where('domain_name', $domain_name)
                        ->where('domain_enabled', true)
                        ->first();

                    if (empty($domain)) {
                        return false;
                        // return $query->where('domain_uuid', 'fake');
                    }

                    return $query->where('domain_uuid', $domain->domain_uuid);
                }),
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                new UsernameRule(),
                Rule::unique(User::class)->where(function ($query) {
                    // $domain_name = $this->request->get('domain_name');
                    $domain_name = request()->get('domain_name');

                    $domain = Domain::where('domain_name', $domain_name)
                        ->where('domain_enabled', true)
                        ->first();

                    if (empty($domain)) {
                        return false;
                        // return $query->where('domain_uuid', 'fake');
                    }

                    return $query->where('domain_uuid', $domain->domain_uuid);
                }),
            ],

            'password' => $this->validationRulesService->getPasswordRules('user'),

            'extensions' => 'required|array',
            'extensions.*.extension' => $this->validationRulesService->getExtensionRules(request()->get('domain_name')),
            'extensions.*.password' => $this->validationRulesService->getPasswordRules('extension'),
            'extensions.*.voicemail_password' => $this->validationRulesService->getPasswordRules('voicemail'),
            'contacts' => 'array',
            'contacts.*.contact_url' => 'url',
        ];

        $resellerCodeRequired = config('fpbx.resellerCodeRequired');
        if ($resellerCodeRequired) {
            $rules['reseller_reference_code'] = [
                'required',
                Rule::exists(DefaultSetting::class, 'default_setting_value')
                    ->where('default_setting_category', 'billing')
                    ->where('default_setting_subcategory', 'reseller_code'),
            ];
        }

        return $rules;
    }
}
