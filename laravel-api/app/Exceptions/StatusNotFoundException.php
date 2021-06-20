<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Traits\BaseException;

class StatusNotFoundException extends NotFoundHttpException
{
  use BaseException;
}
