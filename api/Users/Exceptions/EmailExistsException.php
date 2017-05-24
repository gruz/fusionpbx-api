<?php

namespace Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class EmailExistsException extends UnprocessableEntityHttpException
{
  use BaseException;
}
