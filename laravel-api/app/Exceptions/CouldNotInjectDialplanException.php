<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

use App\Traits\BaseExceptionTrait;

class CouldNotInjectDialplanException extends HttpException
{
  use BaseExceptionTrait;
}
