<?php

namespace Gruz\FPBX\Exceptions\Socket;

use Gruz\FPBX\Traits\BaseExceptionTrait;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InvalidJSONInput extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;

}