<?php

namespace Infrastructure\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

use App\Traits\BaseException;

class InvalidUserException extends HttpException
{
  use BaseException;
}
