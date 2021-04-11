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
        ],
        'contact' => [
            // @link https://docs.fusionpbx.com/en/latest/applications/contacts.html?highlight=contact%20type#contacts
            'group' => env('FPBX_DEFAULT_CONTACT_GROUP','user'),
        ],
        'user' => [
            'creatorName' => env('FPBX_DEFAULT_USER_CREATORNAME', 'admin'),
            'group' => [
                'public' => env('FPBX_DEFAULT_USER_GROUP', 'public'),
                'admin' => env('FPBX_ADMIN_USER_GROUP', 'admin'),
                // 'superadmin' => env('FPBX_SUPERADMIN_USER_GROUP', 'superadmin'),
                // 'agent' => env('FPBX_AGENT_USER_GROUP', 'agent'),
                // 'user' => env('FPBX_USER_USER_GROUP', 'user'),
            ],
        ],
    ],
    'domain' => [
        'enabled' => env('FPBX_DOMAIN_ENABLED', true), // If domain is enabled by default after activation
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
