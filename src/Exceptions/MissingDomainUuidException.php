<?php

namespace Gruz\FPBX\Exceptions;

use Gruz\FPBX\Traits\BaseExceptionTrait;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MissingDomainUuidException extends UnprocessableEntityHttpException
{
    use BaseExceptionTrait;
}
