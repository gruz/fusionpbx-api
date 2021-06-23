<?php

namespace App\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseExceptionTrait;

class NoCommadException extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;
}
