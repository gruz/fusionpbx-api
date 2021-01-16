<?php

namespace Infrastructure\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class NoCommadException extends UnprocessableEntityHttpException
{
  use BaseException;
}
