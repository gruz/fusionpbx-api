<?php

namespace Api\Domain\Requests;

use Api\User\Models\User;
use Illuminate\Support\Arr;
use Api\Domain\Models\Domain;
use Infrastructure\Rules\Hostname;
use Infrastructure\Http\ApiRequest;
use Illuminate\Contracts\Validation\Validator;
use Api\User\Exceptions\WrongSignupDataException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Infrastructure\Rules\ArrayAtLeastOneAccepted;

class DomainSignupRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'domain_name' => [
                'required',
                'unique:Api\\Domain\\Models\\Domain,domain_name'
            ],
            'users' => 'required',
            'users.*.username' => 'required|distinct',
            'users.*.user_email' => 'required|distinct:ignore_case|email',
            'users.*.password' => 'required|min:6|max:25',
            'users' => new ArrayAtLeastOneAccepted('is_admin'),
        ];

        if (!$this->request->get('is_subdomain')) {
            $rules['domain_name'][] = new Hostname();
        }

        return $rules;
    }
}
