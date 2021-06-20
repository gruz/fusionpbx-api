<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class InvalidStatusOSException extends UnprocessableEntityHttpException
{
  use BaseException;
}
