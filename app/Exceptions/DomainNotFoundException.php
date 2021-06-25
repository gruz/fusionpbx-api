<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Traits\BaseExceptionTrait;

class DomainNotFoundException extends NotFoundHttpException
{
  use BaseExceptionTrait;
}
