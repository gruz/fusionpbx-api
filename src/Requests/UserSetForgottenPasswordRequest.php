<?php

namespace Gruz\FPBX\Requests;

use Gruz\FPBX\Models\Domain;
use Illuminate\Validation\Rule;
use Gruz\FPBX\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Gruz\FPBX\Services\ValidationRulesService;

class UserSetForgottenPasswordRequest extends FormRequest
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
            'username' => [
                'required',
            ],
            'code' => 'required',
            'password' => $this->validationRulesService->getPasswordRules('user'),
        ];

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
            'username.exists' => __('Vo1icemail password must be between 4 and 10 digits'),
        ];
    }
}
