<?php

return [
    'namespaces' => get_composer_json_namespaces(),

    'protection_middleware' => [
        'api',
        // 'auth:api',
    ],

    'resource_namespace' => 'resources',

    'language_folder_name' => 'lang',

    'view_folder_name' => 'views'
];
