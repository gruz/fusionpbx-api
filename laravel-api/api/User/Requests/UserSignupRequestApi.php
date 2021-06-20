<?php

namespace Api\User\Requests;

use App\Traits\ApiRequestTrait;
use App\Auth\Requests\UserSignupRequest;

class UserSignupRequestApi extends UserSignupRequest
{
    use ApiRequestTrait;
}
