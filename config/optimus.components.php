<?php

return [
    'namespaces' => [
        'Api' => base_path() . DIRECTORY_SEPARATOR . 'api',
        'App' => base_path() . DIRECTORY_SEPARATOR . 'app'
    ],


    'protection_middleware' => [
        'auth:api'
    ],

    'resource_namespace' => 'resources',

    'language_folder_name' => 'lang',

    'view_folder_name' => 'views'
];
