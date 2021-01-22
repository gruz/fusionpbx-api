<?php

namespace Infrastructure\Exceptions\Socket;

use Infrastructure\Traits\BaseException;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InvalidJSONInput extends UnprocessableEntityHttpException
{
  use BaseException;

}