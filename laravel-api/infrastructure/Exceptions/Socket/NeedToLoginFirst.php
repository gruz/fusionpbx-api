<?php

namespace Infrastructure\Exceptions\Socket;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use App\Traits\BaseException;

class NeedToLoginFirst extends UnprocessableEntityHttpException
{
  use BaseException;
}
