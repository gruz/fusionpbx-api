<?php

namespace Gruz\FPBX\Requests;

use Gruz\FPBX\Models\Domain;
use Gruz\FPBX\Services\Fpbx\UserService;
use Gruz\FPBX\Traits\ApiRequestTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
{
    use ApiRequestTrait;

    public function authorize()
    {
        return true;

        // ##mygruz20210830223655  Do not delete the code below. Used for reference
        // We allow a non-verified user to login but all other actions will be blocked by `verified` middleware
        // Maybe we will want to block login for unverified users in the future, but native Laravel Breeze behavior allows
        // unverified login

        /**
         * @var UserService
         */
        $userService = app(UserService::class);
        $userModel = $userService->getUserByUsernameAndDomain($this->username, $this->domain_name);
        $return = $userModel->getAttribute('user_enabled') === 'true' ? true : false;

        if (!$return) {
            $this->failedAuthorizationMessage = __('User disabled');
        }

        return $return;
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
            'username.exists' => __('Vo1icemail password must be between 4 and 10 digits'),
        ];
    }
}
