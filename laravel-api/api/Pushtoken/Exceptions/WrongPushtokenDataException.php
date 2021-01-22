<?php

namespace Api\Pushtoken\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class WrongPushtokenDataException extends UnprocessableEntityHttpException
{
  use BaseException;
}
