<?php

namespace Api\Domain\Requests;

use Illuminate\Validation\Rule;
use Infrastructure\Rules\HostnameRule;
use Infrastructure\Rules\UsernameRule;
use Api\Settings\Models\DefaultSetting;
use App\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Infrastructure\Services\ValidationRulesService;
use Infrastructure\Rules\ArrayAtLeastOneAcceptedRule;

class DomainSignupRequest extends FormRequest
{
    use ApiRequestTrait;

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
                'unique:Api\\Domain\\Models\\Domain,domain_name'
            ],
            'users' => 'required|array',
            // 'is_subdomain' => 'required',
            // 'users.*.username' => 'required|distinct|alpha_dash',
            'users.*.username' => [
                'required',
                'string',
                new UsernameRule(),
            ],
            'users.*.user_email' => 'required|distinct:ignore_case|email',
            'users.*.password' => $this->validationRulesService->getPasswordRules('user'),
            'users.*.extensions.*.extension' => $this->validationRulesService->getExtensionRules(),
            'users.*.extensions.*.password' => $this->validationRulesService->getPasswordRules('extension'),
            'users.*.extensions.*.voicemail_password' => $this->validationRulesService->getPasswordRules('voicemail'),
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
