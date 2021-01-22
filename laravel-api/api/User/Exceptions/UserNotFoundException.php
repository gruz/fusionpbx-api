<?php

namespace Api\User\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Infrastructure\Traits\BaseException;

class UserNotFoundException extends NotFoundHttpException
{
  use BaseException;
}
