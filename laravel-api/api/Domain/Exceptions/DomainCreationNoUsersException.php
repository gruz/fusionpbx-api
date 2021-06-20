<?php

namespace Api\Domain\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

/**
 * TODO Remove
 *
 * @derecated
 */
class DomainCreationNoUsersException extends UnprocessableEntityHttpException
{
  use BaseException;
}
