<?php

namespace Infrastructure\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class TooManyMessages extends UnprocessableEntityHttpException
{
  use BaseException;
}
