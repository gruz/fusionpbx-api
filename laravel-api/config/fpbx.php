<?php

return [
    'debug' => [
        'swaggerProcessor' => false,
    ],
    /**
     * If enabled, then users or domains must provide a reseller code to be registered.
     * For now we add available reseller codes to `v_default_settings`
     * ```
     * INSERT INTO public.v_default_settings (default_setting_uuid,app_uuid,default_setting_category,default_setting_subcategory,default_setting_name,default_setting_value,default_setting_order,default_setting_enabled,default_setting_description) VALUES
     *     ('94f927d2-f0dc-4c82-83ea-063d64cacb44'::uuid,NULL,'billing','reseller_code','array','Code01',0,true,NULL),
     *     ('68fa39da-5201-4b7a-8c22-484ef7c8d24c'::uuid,NULL,'billing','reseller_code','array','Code02',0,true,NULL);
     * ```
     */
    'resellerCodeRequired' => true,
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
            'group' => env('FPBX_DEFAULT_CONTACT_GROUP', 'user'),
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
        'description' => env('FPBX_DOMAIN_DESCRIPTION', 'Created via api at ' . date('Y-m-d H:i:s', time())),
    ],

    'extension' => [
        'min' => 10000000,
        'max' => 99999999,
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
    ],
    'time_format' => 'Y-m-d H:i:s.uO',


];
