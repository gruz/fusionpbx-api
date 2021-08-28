<?php

use Optimus\Heimdal\Formatters;
use Symfony\Component\HttpKernel\Exception as SymfonyException;
use Gruz\FPBX\Exceptions\Formatters\AuthenticationExceptionFormatter;
use Optimus\Heimdal\Formatters\UnprocessableEntityHttpExceptionFormatter;

return [
    // Has to be in prioritized order, e.g. highest priority first.
    'formatters' => [
        SymfonyException\UnprocessableEntityHttpException::class => UnprocessableEntityHttpExceptionFormatter::class,
        SymfonyException\HttpException::class => \Gruz\FPBX\Exceptions\Formatters\ExceptionFormatter::class,
        Illuminate\Auth\AuthenticationException::class => AuthenticationExceptionFormatter::class,
        Throwable::class => \Gruz\FPBX\Exceptions\Formatters\ExceptionFormatter::class,
    ],
];
