<?php

namespace Gruz\FPBX\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Gruz\FPBX\Traits\BaseExceptionTrait;

class DomainNotFoundException extends NotFoundHttpException
{
  use BaseExceptionTrait;
}
