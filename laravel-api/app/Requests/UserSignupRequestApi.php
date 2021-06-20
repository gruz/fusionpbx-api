<?php

namespace App\Requests;

use App\Traits\ApiRequestTrait;
use App\Auth\Requests\UserSignupRequest;

class UserSignupRequestApi extends UserSignupRequest
{
    use ApiRequestTrait;
}
