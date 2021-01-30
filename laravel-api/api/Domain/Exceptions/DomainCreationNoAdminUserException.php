<?php

namespace Api\Domain\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class DomainCreationNoAdminUserException extends UnprocessableEntityHttpException
{
  use BaseException;
}
