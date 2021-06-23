<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseExceptionTrait;

class InvalidPushtokenTypeException extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;
}
