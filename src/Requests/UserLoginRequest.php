<?php

namespace Gruz\FPBX\Requests;

use Gruz\FPBX\Models\Domain;
use Gruz\FPBX\Traits\ApiRequestTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
{
    use ApiRequestTrait;

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
            'username' => 'required',
            'password' => 'required',
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
            'domain_name' => __('Voicemail password must be between 4 and 10 digits'),
        ];
    }
}
