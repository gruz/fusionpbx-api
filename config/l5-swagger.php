<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'paths' => [
                /*
                 * Absolute paths to directory containing the swagger annotations are stored.
                */
                'annotations' => get_composer_json_namespaces(),
            ],
        ],
    ],
    'defaults' => [
        'scanOptions' => [
            /**
             * Custom query path processors classes.
             *
             * @link https://github.com/zircote/swagger-php/tree/master/Examples/schema-query-parameter-processor
             * @see \OpenApi\scan
             */
            'processors' => [
                \Gruz\FPBX\SwaggerProcessors\SchemaQueryParameter::class,
            ],
        ],
        /*
         * Uncomment to add constants which can be used in annotations
         */
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://my-default-host.com'),
            'SW_VERSION' => env('SW_VERSION', \Gruz\FPBX\Helpers\Version::getPackageVersion()),
            'APP_URL' => env('APP_URL', 'https://localhost') . '/api/' . config('fpbx.api_version'),
            // 'APP_NAME' => env('APP_NAME'),
        ],
    ],
];
