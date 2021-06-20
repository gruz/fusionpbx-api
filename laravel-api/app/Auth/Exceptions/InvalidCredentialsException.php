<?php

namespace App\Auth\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use App\Traits\BaseException;

class InvalidCredentialsException extends UnauthorizedHttpException
{
  use BaseException;
}
