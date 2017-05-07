<?php

namespace Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InvalidGroupException extends UnprocessableEntityHttpException
{
    public function __construct($groupId)
    {
        parent::__construct("The group with ID $groupId is not a group");
    }
}
