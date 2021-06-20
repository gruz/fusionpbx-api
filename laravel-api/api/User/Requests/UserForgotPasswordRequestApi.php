<?php

namespace Api\User\Requests;

use App\Traits\ApiRequestTrait;
use Infrastructure\Auth\Requests\UserForgotPasswordRequest;

class UserForgotPasswordRequestApi extends UserForgotPasswordRequest
{
    use ApiRequestTrait;
}
