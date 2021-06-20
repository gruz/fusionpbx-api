<?php

namespace Api\User\Requests;

use App\Traits\ApiRequestTrait;
use Infrastructure\Auth\Requests\UserSignupRequest;

class UserSignupRequestApi extends UserSignupRequest
{
    use ApiRequestTrait;
}
