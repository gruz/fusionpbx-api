<?php

namespace App\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class NoCommadException extends UnprocessableEntityHttpException
{
  use BaseException;
}
