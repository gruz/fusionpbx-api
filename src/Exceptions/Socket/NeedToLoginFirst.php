<?php

namespace Gruz\FPBX\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use Gruz\FPBX\Traits\BaseExceptionTrait;

class NeedToLoginFirst extends UnprocessableEntityHttpException
{
  use BaseExceptionTrait;
}
