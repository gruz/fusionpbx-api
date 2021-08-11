<?php

namespace Gruz\FPBX\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Gruz\FPBX\Traits\BaseExceptionTrait;

class InvalidPushtokenTypeException extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;
}
