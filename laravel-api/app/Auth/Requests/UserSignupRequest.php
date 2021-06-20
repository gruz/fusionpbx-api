<?php

namespace App\Auth\Requests;

use App\Models\User;
use App\Models\Domain;
use Illuminate\Validation\Rule;
use App\Rules\UsernameRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CheckReferenceCodeRule;
use App\Services\ValidationRulesService;

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
        $domain_enabled = true;

        if (config('domain_enabled_field_type') === 'text') {
            $domain_enabled = $domain_enabled ? 'true' : 'false';
        }
        $rules = [
            'domain_name' => [
                'required',
                Rule::exists(Domain::class, 'domain_name')
                    ->where('domain_enabled', $domain_enabled),
            ],
            'user_email' => [
                'required',
                Rule::unique(User::class)->where(function ($query) use ($domain_enabled) {
                    // $domain_name = $this->request->get('domain_name');
                    $domain_name = $this->get('domain_name');

                    $domain = Domain::where('domain_name', $domain_name)
                        ->where('domain_enabled', $domain_enabled)
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
                new UsernameRule(),
                Rule::unique(User::class)->where(function ($query) use ($domain_enabled) {
                    // $domain_name = $this->request->get('domain_name');
                    $domain_name = $this->get('domain_name');

                    $domain = Domain::where('domain_name', $domain_name)
                        ->where('domain_enabled', $domain_enabled)
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
            'extensions.*.extension' => $this->validationRulesService->getExtensionRules($this->get('domain_name')),
            'extensions.*.password' => $this->validationRulesService->getPasswordRules('extension'),
            'extensions.*.voicemail_password' => $this->validationRulesService->getPasswordRules('voicemail'),
            'contacts' => 'array',
            'contacts.*.contact_url' => 'url',
        ];

        $resellerCodeRequired = config('fpbx.resellerCode.required');
        if ($resellerCodeRequired) {
            $rules['reseller_reference_code'] = [
                'required',
                new CheckReferenceCodeRule(),
            ];
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'extensions.*.voicemail_password.digits_between' => __('Voicemail password must be between 4 and 10 digits'),
            'password.regex' => __('Min 6 symbols, case sensitive, at least one lowercase, one uppercase and one digit'),
        ];
    }
}
