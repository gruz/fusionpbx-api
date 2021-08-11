<?php

namespace Gruz\FPBX\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Gruz\FPBX\Traits\BaseExceptionTrait;

class GroupNotFoundException extends NotFoundHttpException
{
  use BaseExceptionTrait;
}
