<?php

namespace App\Requests;

use App\Traits\ApiRequestTrait;
use App\Requests\UserSignupRequest;

class UserSignupRequestApi extends UserSignupRequest
{
    use ApiRequestTrait;
}
