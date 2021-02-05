<?php

namespace Api\Domain\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Infrastructure\Traits\BaseException;

class DomainNotFoundException extends NotFoundHttpException
{
  use BaseException;
}
