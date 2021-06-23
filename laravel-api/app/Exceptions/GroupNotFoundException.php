<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Traits\BaseExceptionTrait;

class GroupNotFoundException extends NotFoundHttpException
{
  use BaseExceptionTrait;
}
