<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseExceptionTrait;

class InvalidPushtokenClassException extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;
}
