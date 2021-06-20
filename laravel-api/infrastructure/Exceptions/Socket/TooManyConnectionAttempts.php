<?php

namespace Infrastructure\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class TooManyConnectionAttempts extends UnprocessableEntityHttpException
{
  use BaseException;
}
