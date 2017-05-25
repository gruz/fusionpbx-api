<?php

namespace Api\Extensions\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class InvalidExtension_userException extends UnprocessableEntityHttpException
{
  use BaseException;
}
