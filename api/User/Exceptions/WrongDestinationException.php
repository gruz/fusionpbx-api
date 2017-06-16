<?php

namespace Api\User\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class WrongDestinationException extends UnprocessableEntityHttpException
{
  use BaseException;
}
