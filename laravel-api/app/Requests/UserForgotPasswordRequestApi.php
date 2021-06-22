<?php

namespace App\Requests;

use App\Traits\ApiRequestTrait;
use App\Requests\UserForgotPasswordRequest;

class UserForgotPasswordRequestApi extends UserForgotPasswordRequest
{
    use ApiRequestTrait;
}
