<?php

namespace Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Infrastructure\Traits\BaseException;

class GroupNotFoundException extends NotFoundHttpException
{
  use BaseException;
}
