<?php

namespace Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Traits\BaseException;

class DomainNotFoundException extends NotFoundHttpException
{
  use BaseException;
}
