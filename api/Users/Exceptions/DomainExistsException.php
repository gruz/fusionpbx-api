<?php

namespace Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DomainExistsException extends UnprocessableEntityHttpException
{
    public function __construct($domain_name)
    {
        parent::__construct("Domain $domain_name already exists");
    }
}
