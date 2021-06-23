<?php

namespace App\Exceptions\Socket;

use App\Traits\BaseExceptionTrait;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InvalidJSONInput extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;

}