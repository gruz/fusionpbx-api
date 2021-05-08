<?php

use Optimus\Heimdal\Formatters;
use Symfony\Component\HttpKernel\Exception as SymfonyException;
use Infrastructure\Exceptions\Formatters\UnprocessableEntityHttpExceptionFormatter;

return [
    'add_cors_headers' => false,

    // Has to be in prioritized order, e.g. highest priority first.
    'formatters' => [
        SymfonyException\UnprocessableEntityHttpException::class => UnprocessableEntityHttpExceptionFormatter::class,
        SymfonyException\HttpException::class => Formatters\HttpExceptionFormatter::class,
        Exception::class => Formatters\ExceptionFormatter::class,
    ],

    'response_factory' => \Optimus\Heimdal\ResponseFactory::class,

    'reporters' => [
        // 'sentry' => [
        //     'class'  => \Optimus\Heimdal\Reporters\SentryReporter::class,
        //     'config' => [
        //         'dsn' => 'https://ddc67c85f42d48c3b3210c075376e055:feb86728b7a048389de51f74f3ba399f@sentry.io/160790',
        //         // For extra options see https://docs.sentry.io/clients/php/config/
        //         // php version and environment are automatically added.
        //         'sentry_options' => []
        //     ]
        // ]
    ],

    'server_error_production' => 'An error occurred.'
];
