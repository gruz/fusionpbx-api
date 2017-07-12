<?php

namespace App\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class TooManyMessages extends UnprocessableEntityHttpException
{
  use BaseException;
}
