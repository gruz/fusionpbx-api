<?php

namespace Gruz\FPBX\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Gruz\FPBX\Traits\BaseExceptionTrait;

class InvalidStatusException extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;
}