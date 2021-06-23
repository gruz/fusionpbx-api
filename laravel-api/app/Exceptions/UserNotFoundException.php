<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Traits\BaseExceptionTrait;

class UserNotFoundException extends NotFoundHttpException
{
  use BaseExceptionTrait;
}
