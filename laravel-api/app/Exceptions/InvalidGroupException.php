<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseExceptionTrait;

class InvalidGroupException extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;
}
