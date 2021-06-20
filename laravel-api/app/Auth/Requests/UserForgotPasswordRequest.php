<?php

namespace App\Auth\Requests;

use App\Models\User;
use App\Models\Domain;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UserExistsInDomainRule;

class UserForgotPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $domain_name = $this->get('domain_name');
        $domain_enabled = true;
        if (config('domain_enabled_field_type') === 'text') {
            $domain_enabled = $domain_enabled ? 'true' : 'false';
        }
        return [
            'domain_name' => [
                'required',
                Rule::exists(Domain::class, 'domain_name')->where('domain_enabled', $domain_enabled),
            ],
            'user_email' =>
            [
                'bail',
                'required',
                'email',
                Rule::exists(User::class, 'user_email')->where('user_enabled', 'true'),
                new UserExistsInDomainRule($domain_name)
            ]
        ];
    }
}
