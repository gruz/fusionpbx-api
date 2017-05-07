<?php

namespace Api\Users\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TestException extends UnprocessableEntityHttpException
{

    public function __construct($message = null, Exception $previous = null)
    {
        parent::__construct('Custom validation', $previous, 30005);
    }
}
