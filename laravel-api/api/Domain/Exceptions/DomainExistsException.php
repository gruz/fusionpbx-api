<?php

namespace Api\Domain\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class DomainExistsException extends UnprocessableEntityHttpException
{
  use BaseException;
}
