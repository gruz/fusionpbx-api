<?php

namespace Api\User\Requests;

use Infrastructure\Traits\ApiRequestTrait;
use Infrastructure\Auth\Requests\UserSignupRequestAbstract;

class UserSignupRequest extends UserSignupRequestAbstract
{
    use ApiRequestTrait;
}
