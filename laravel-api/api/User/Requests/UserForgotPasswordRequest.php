<?php

namespace Api\User\Requests;

use Api\User\Models\User;
use Api\Domain\Models\Domain;
use Illuminate\Validation\Rule;
use Infrastructure\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Infrastructure\Rules\UserExistsInDomainRule;

class UserForgotPasswordRequest extends FormRequest
{
    use ApiRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $domain_name = request()->get('domain_name');
        return [
            'domain_name' => [
                'required',
                Rule::exists(Domain::class, 'domain_name')->where('domain_enabled', true),
            ],
            'user_email' =>
            [
                'required',
                'email',
                Rule::exists(User::class, 'user_email')->where('user_enabled', 'true'),
                new UserExistsInDomainRule($domain_name)
            ]
        ];
    }
}
