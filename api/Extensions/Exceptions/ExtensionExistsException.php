<?php

namespace Api\Extensions\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class ExtensionExistsException extends UnprocessableEntityHttpException
{
  use BaseException;
}
