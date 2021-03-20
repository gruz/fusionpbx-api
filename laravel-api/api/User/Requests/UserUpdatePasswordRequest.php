<?php

namespace Api\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Infrastructure\Api\Rules\FusionPBXPasswordRequirements;

class UserUpdatePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $domainName = $this->post('domain_name');
        $userEmail = $this->post('user_email');
        $passwordFor = 'users';

        // if ($this->exists(['domain_name', 'user_email'])) {
        //     $passwords = $this->checkPasswordSettings($this->only('domain_name', 'user_email'));
            
            return [
                'user_email' => 'required|email|exists:password_resets,email',
                'token' => 'required',
                // https://docs.fusionpbx.com/en/latest/advanced/default_settings.html#id26
                // password settings needs to be fetched from fusion pbx and set here
                'password' => ['required', 'confirmed',
                                new FusionPBXPasswordRequirements($domainName, $userEmail, $passwordFor)],
                'domain_name' => 'required|exists:password_resets'
            ];
        // }
        
        // return $this->failedValidation($this->validator);
        
    }

    public function messages()
    {
        return [
            'domain_name.exists' => __('Invalid data'),
            'user_email.exists' => __('Invalid data'),
        ];
    }
   
}
