<?php

namespace Api\User\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class WrongDestinationException extends UnprocessableEntityHttpException
{
  use BaseException;
}
