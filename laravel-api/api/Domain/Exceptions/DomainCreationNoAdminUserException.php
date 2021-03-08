<?php

namespace Api\Domain\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

/**
 * TODO Remove
 *
 * @derecated
 */
class DomainCreationNoAdminUserException extends UnprocessableEntityHttpException
{
  use BaseException;
}
