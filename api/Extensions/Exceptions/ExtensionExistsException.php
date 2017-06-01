<?php

namespace Api\Extensions\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class ExtensionExistsException extends UnprocessableEntityHttpException
{
  use BaseException;
}
