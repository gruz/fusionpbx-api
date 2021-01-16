<?php

namespace Api\User\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class UserExistsException extends UnprocessableEntityHttpException
{
  use BaseException;
}
