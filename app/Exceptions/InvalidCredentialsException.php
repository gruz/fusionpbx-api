<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use App\Traits\BaseExceptionTrait;

class InvalidCredentialsException extends UnauthorizedHttpException
{
  use BaseExceptionTrait;
}
