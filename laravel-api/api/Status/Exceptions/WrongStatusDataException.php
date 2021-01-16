<?php

namespace Api\Status\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Infrastructure\Traits\BaseException;

class WrongStatusDataException extends UnprocessableEntityHttpException
{
  use BaseException;
}
