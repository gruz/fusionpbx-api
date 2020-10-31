<?php

namespace Api\User\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class ActivationHashWrongException extends UnprocessableEntityHttpException
{
  use BaseException;
}
