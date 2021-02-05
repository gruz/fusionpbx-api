<?php

return [

    'domain' => [
        'enabled' => env('FPBX_DOMAIN_ENABLED', true),
        'description' => env('FPBX_DOMAIN_DESCRIPTION', 'Created via api at ' . date( 'Y-m-d H:i:s', time() )),
    ],

    /**
     * Overrides model level defined fillable fields
     */
    'table' => [
        // Example
        // 'v_domain_settings' => [
        //     'add_fillable' => [
        //         'app_uuid',
        //     ],
        //     'remove_fillable' => [
        //         'domain_setting_category',
        //     ],
        // ]
        // 'v_domains' => [
        //     'add_fillable' => [
        //         'domain_enabled'
        //     ]
        // ]
    ]

];
