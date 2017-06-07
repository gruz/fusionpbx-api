<?php

namespace Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Traits\BaseException;

class ActivationHashNotFoundException extends NotFoundHttpException
{
  use BaseException;
}
