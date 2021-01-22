<?php

namespace Api\Extension\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Infrastructure\Traits\BaseException;

class ExtensionNotFoundException extends NotFoundHttpException
{
  use BaseException;
}
