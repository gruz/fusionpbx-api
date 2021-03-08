<?php

namespace Infrastructure\Exceptions;

use Infrastructure\Traits\BaseException;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ActivationHashExpired extends UnprocessableEntityHttpException
{
  use BaseException;
}
