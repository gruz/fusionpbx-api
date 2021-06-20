<?php

namespace App\Exceptions;

use App\Traits\BaseException;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MissingDomainUuidException extends UnprocessableEntityHttpException
{
    use BaseException;
}
