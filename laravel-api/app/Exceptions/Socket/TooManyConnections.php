<?php

namespace App\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class TooManyConnections extends UnprocessableEntityHttpException
{
  use BaseException;
}
