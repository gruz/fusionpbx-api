<?php

namespace Infrastructure\Exceptions;

use Infrastructure\Traits\BaseException;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MissingDomainUuidException extends UnprocessableEntityHttpException
{
    use BaseException;
}
