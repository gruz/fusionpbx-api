<?php

namespace Gruz\FPBX\Requests;

use Gruz\FPBX\Traits\ApiRequestTrait;
use Gruz\FPBX\Rules\UserAlreadyEnabledRule;
use Illuminate\Foundation\Http\FormRequest;
use Gruz\FPBX\Rules\UserSignupHashExpiredRule;

class UserActivateRequest extends FormRequest
{
    use ApiRequestTrait;

    protected $stopOnFirstFailure = true;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'user_uuid' => [
                'required',
                'uuid',
                'exists:\Gruz\FPBX\Models\User',
            ],
            'user_enabled' => [
                'bail',
                'required',
                new UserAlreadyEnabledRule($this->user_uuid),
                new UserSignupHashExpiredRule($this->user_uuid),
            ],
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['user_uuid'] = $this->route('user_uuid');
        $data['user_enabled'] = $this->route('user_enabled');

        return $data;
    }
}
