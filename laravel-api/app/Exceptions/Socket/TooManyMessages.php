<?php

namespace App\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseExceptionTrait;

class TooManyMessages extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;
}
