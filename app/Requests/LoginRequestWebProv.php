<?php

namespace App\Requests;

class LoginRequestWebProv extends UserLoginRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'username' => $this->cloud_username ? $this->cloud_username : '',
            'password' => $this->cloud_password ? $this->cloud_password : null,
            'domain_name' => $this->cloud_id ? $this->cloud_id : null,
        ]);

        parent::prepareForValidation();
    }

}
