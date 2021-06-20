<?php

namespace App\Requests;

use App\Traits\ApiRequestTrait;
use App\Auth\Requests\UserForgotPasswordRequest;

class UserForgotPasswordRequestApi extends UserForgotPasswordRequest
{
    use ApiRequestTrait;
}
