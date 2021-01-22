<?php

namespace Api\Status\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Infrastructure\Traits\BaseException;

class StatusNotFoundException extends NotFoundHttpException
{
  use BaseException;
}
