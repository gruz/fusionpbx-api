<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class InvalidPushtokenClassException extends UnprocessableEntityHttpException
{
  use BaseException;
}
