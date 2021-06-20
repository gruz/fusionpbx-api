<?php

namespace Api\Extension\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Traits\BaseException;

class ExtensionNotFoundException extends NotFoundHttpException
{
  use BaseException;
}
