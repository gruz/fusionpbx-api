<?php

namespace Api\Users\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('The role was not found.');
    }
}
