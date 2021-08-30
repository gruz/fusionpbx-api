<?php

namespace Gruz\FPBX\Requests;

use Gruz\FPBX\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Gruz\FPBX\Rules\DomainAlreadyEnabledRule;
use Gruz\FPBX\Rules\DomainSignupHashExpiredRule;
use Gruz\FPBX\Rules\DomainSignupHashHasEmailExistsRule;

class DomainActivateRequest extends FormRequest
{
    use ApiRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'code' => [
                'bail',
                'required',
                'integer',
                'exists:\Gruz\FPBX\Models\PostponedAction,code',
                new DomainSignupHashExpiredRule(),
                new DomainAlreadyEnabledRule(),
            ],
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['code'] = $this->route('code');
        return $data;
    }
}
