<?php

namespace Api\Dialplan\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

use Infrastructure\Traits\BaseException;

class CouldNotInjectDialplanException extends HttpException
{
  use BaseException;
}
