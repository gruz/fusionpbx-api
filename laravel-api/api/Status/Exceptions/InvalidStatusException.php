<?php

namespace Api\Status\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class InvalidStatusException extends UnprocessableEntityHttpException
{
  use BaseException;
}
