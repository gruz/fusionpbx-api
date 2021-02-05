<?php

namespace Infrastructure\Auth\Controllers;

use Infrastructure\Http\Controller;
use Infrastructure\Auth\Services\PasswordService;
use Infrastructure\Auth\Requests\PasswordForgotRequest;
use Infrastructure\Auth\Requests\PasswordResetRequest;

class PasswordController extends Controller
{
    private $passwordService;

    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }

    public function forgotPassword(PasswordForgotRequest $request) 
    {
        $email = $request->get('user_email');   
        $domain_name = $request->get('domain_name');

        return $this->response($this->passwordService->generateResetToken($email, $domain_name));
    }

    public function resetPassword(PasswordResetRequest $request) 
    {
        $email = $request->get('user_email');   

        return $this->response($this->passwordService->resetPassword($email));
    }

}