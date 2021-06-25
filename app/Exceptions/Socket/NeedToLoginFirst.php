<?php

namespace App\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseExceptionTrait;

class NeedToLoginFirst extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;
}
