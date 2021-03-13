<?php

namespace Api\Extension\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class InvalidExtensionUserException extends UnprocessableEntityHttpException
{
  use BaseException;
}
