<?php

namespace App\Exceptions;

use App\Traits\BaseExceptionTrait;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MissingDomainUuidException extends UnprocessableEntityHttpException
{
    use BaseExceptionTrait;
}
