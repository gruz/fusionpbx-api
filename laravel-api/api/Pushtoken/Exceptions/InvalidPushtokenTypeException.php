<?php

namespace Api\Pushtoken\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class InvalidPushtokenTypeException extends UnprocessableEntityHttpException
{
  use BaseException;
}
