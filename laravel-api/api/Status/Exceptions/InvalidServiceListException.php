<?php

namespace Api\Status\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class InvalidServiceListException extends UnprocessableEntityHttpException
{
  use BaseException;
}
