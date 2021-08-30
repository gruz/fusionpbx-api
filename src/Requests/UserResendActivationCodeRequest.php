<?php

namespace Gruz\FPBX\Requests;

use Gruz\FPBX\Models\Domain;
use Illuminate\Validation\Rule;
use Gruz\FPBX\Services\Fpbx\UserService;
use Gruz\FPBX\Rules\UserAlreadyEnabledRule;
use Gruz\FPBX\Rules\UserExistsInDomainRule;
use Illuminate\Foundation\Http\FormRequest;

class UserResendActivationCodeRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $domain_name = $this->get('domain_name');
        $user_email = $this->get('user_email');
        $domain_enabled = true;
        if (config('domain_enabled_field_type') === 'text') {
            $domain_enabled = $domain_enabled ? 'true' : 'false';
        }

        /**
         * @var UserService
         */
        $userService = app(UserService::class);

        $userModel = $userService->getUserByEmailAndDomain($user_email, $domain_name);
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
                // Rule::exists(User::class, 'user_email')->where('user_enabled', 'true'),
                new UserExistsInDomainRule($domain_name),
                new UserAlreadyEnabledRule(optional($userModel)->user_uuid),
            ],
        ];
    }
}
