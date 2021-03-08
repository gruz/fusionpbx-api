<?php

return [
    'debug' => [
        'swaggerProcessor' => false,
    ],
    'default' => [
        'contact' => [
            'contact_type' => 'customer',
        ],
        'domain' => [
            'mothership_domain' => env('MOTHERSHIP_DOMAIN', 'localhost'),
            'new_is_subdomain' => env('NEW_IS_SUBDOMAIN', false),
            'activation_expire' => '1 day',
        ]
    ],
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
        //     'mergeFillable' => [
        //         'app_uuid',
        //     ],
        //     ' mergeGuarded' => [
        //         'domain_setting_category',
        //     ],
        // ]
        // 'v_domains' => [
        //     'mergeGuarded' => [
        //         'domain_enabled'
        //     ]
        // ],
        // 'v_users' => [
        //     'mergeGuarded' => [
        //         'add_user'
        //     ],
        //     'makeHidden' => [
        //         'add_user'
        //     ],
        //     'makeVisible' => [
        //         'salt'
        //     ],
        // ]
    ]

];
