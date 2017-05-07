<?php

namespace Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InvalidRoleException extends UnprocessableEntityHttpException
{
    public function __construct($roleId)
    {
        parent::__construct("The role with ID $roleId is not a role");
    }
}
