<?php

namespace Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class UserDisabledException extends UnprocessableEntityHttpException
{
  use BaseException;
}
