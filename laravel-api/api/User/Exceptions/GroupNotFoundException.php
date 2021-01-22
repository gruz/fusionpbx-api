<?php

namespace Api\User\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Infrastructure\Traits\BaseException;

class GroupNotFoundException extends NotFoundHttpException
{
  use BaseException;
}
