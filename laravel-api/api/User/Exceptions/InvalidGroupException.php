<?php

namespace Api\User\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class InvalidGroupException extends UnprocessableEntityHttpException
{
  use BaseException;
}
