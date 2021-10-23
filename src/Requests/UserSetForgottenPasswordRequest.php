<?php

namespace Gruz\FPBX\Requests;

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
        $rules = [
            'password' => $this->validationRulesService->getPasswordRules('user'),
        ];

        return $rules;
    }
}
