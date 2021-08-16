<?php

return [
    'api_version' => 'v1',
    'debug' => [
        'swaggerProcessor' => false,
    ],

    /**
     * If set, then any API request demands the token to be passed.
     * This can be useful if you have a web-admin panel which utilizes the API and want to block any world requests to the API
     * except the ones from the web-admin.
     * No value means the protection is disabled
     * @example `curl -k --request GET 'https://192.168.0.160:444/api' --header 'X-apitoken: 98eb1ab2cd1b260165b34d128cb1c957'`
     */
    'api_token' => env('API_TOKEN', null),

    /**
     * If enabled, then users or domains must provide a reseller code to be registered.
     * For now we add available reseller codes to `v_default_settings`
     * ```
     * INSERT INTO public.v_default_settings (default_setting_uuid,app_uuid,default_setting_category,default_setting_subcategory,default_setting_name,default_setting_value,default_setting_order,default_setting_enabled,default_setting_description) VALUES
     *     ('94f927d2-f0dc-4c82-83ea-063d64cacb44'::uuid,NULL,'billing','reseller_code','array','Code01',0,true,NULL),
     *     ('68fa39da-5201-4b7a-8c22-484ef7c8d24c'::uuid,NULL,'billing','reseller_code','array','Code02',0,true,NULL);
     * ```
     */
    'resellerCode' => [
        /**
         * Whether a reseller code is a must to be registered
         */
        'required' => env('FPBX_RESELLER_CODE_REQUIRED', false),
        /**
         * Whether to look for a reseller code passed by the user in v_default_settings table
         */
        'checkInDefaultSettings' => true,
        /**
         * If CGRT enabled, check if there is a reseller code passed by the user matches any account_code in CGRT.
         * In this case only users recommended by other users can register.
         */
        'checkInCGRT' => true,
    ],

    'default' => [
        'contact' => [
            'contact_type' => 'customer',
        ],
        'domain' => [
            'enabled' => env('FPBX_DOMAIN_ENABLED', true), // If domain is enabled by default after activation
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
        'description' => env('FPBX_DOMAIN_DESCRIPTION', 'Created via api at ' . date('Y-m-d H:i:s', time())),
        // Allow select domain in interface via a select list
        'allow_select' => false,
    ],
    'user' => [
        'include_username_in_reset_password_email' => env('FPBX_USER_INCLUDE_USERNAME_IN_RESET_PASSWORD_EMAIL', false),
    ],
    'extension' => [
        'min' => 100000,
        'max' => 999999,
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
    'hook_command' => env('FPBX_HOOK', 'php /var/www/laravel-api/bin/fpbx_hook.php /var/www/fusionpbx'),
    'cgrt' => [
        'default' => [
            'client_add' => [
                // "tenant" => $this->getTenant($user->domainName),
                // "country" => "US",
                // "account_code" => "3847623914",
                // "account_alias" => null,
                // "name" => $name,
                // "cgrt_username" => $user->username,
                // "company_name" => "Test Company",
                // "address_line_1" => "49 Any Street",
                // "address_line_2" => $reseller_code ? 'Reseller code: ' . $reseller_code : null,
                // "city" => "New York",
                // "state_province" => "New York",
                // "postcode_zip" => null,
                // "telephone_number" => "5666556",
                // "tax_id" => null,
                // "main_email" => $user->user_email,
                // "billing_email" => null,
                // "invoice_email_cc" => null,
                // "noc_email" => null,
                // "rates_email" => null,
                "send_verification_email" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_SEND_VERIFICATION_EMAIL', false),
                "enable_calls" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_ENABLE_CALLS', true),
                "enabled" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_ENABLED', true),
                "suspended" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_SUSPENDED', false),
                "send_welcome_email" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_SEND_WELCOME_EMAIL', false),
                "notify_when_dids_are_added" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_NOTIFY_WHEN_DIDS_ARE_ADDED', true),
                'account_type' => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_ACCOUNT_TYPE', 'PREPAID'),
                "orig_credit_limit" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_ORIG_CREDIT_LIMIT', "0.0000"),
                "reminder_did_alert_days" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_REMINDER_DID_ALERT_DAYS', 0),
                "show_balance" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_SHOW_BALANCE', true),
                "verified_for_payments" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_VERIFIED_FOR_PAYMENTS', true),
                "invoice_tax_handling" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_INVOICE_TAX_HANDLING', null),
                "max_channels" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_MAX_CHANNELS', 30),
                "max_cps" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_MAX_CPS', 10),
                "primary_route" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_PRIMARY_ROUTE', "congestion"),
                "primary_destination" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_PRIMARY_DESTINATION', null),
                "backup_route" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_BACKUP_ROUTE', "congestion"),
                "backup_destination" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_BACKUP_DESTINATION', null),
                "tertiary_route" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_TERTIARY_ROUTE', "congestion"),
                "tertiary_destination" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_TERTIARY_DESTINATION', null),
                "low_balance_notification" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_LOW_BALANCE_NOTIFICATION', "2.0000"),
                "frequency" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_FREQUENCY', "24h"),
                "account_blocked_alert" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_ACCOUNT_BLOCKED_ALERT', true),
                "play_audio_messages" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_PLAY_AUDIO_MESSAGES', false),
                "is_provider" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_IS_PROVIDER', false),
                "max_per_minute_rate" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_MAX_PER_MINUTE_RATE', 1),
                "max_connection_fee" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_MAX_CONNECTION_FEE', 0.5),
                "max_daily_spend" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_MAX_DAILY_SPEND', 50),
                "last_invoice_date" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_LAST_INVOICE_DATE', null),
                "last_balance_alert_sent" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_LAST_BALANCE_ALERT_SENT', null),
                "last_daily_spend_alert_sent" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_LAST_DAILY_SPEND_ALERT_SENT', null),
                "create_sip_endpoint" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_CREATE_SIP_ENDPOINT', false),
                "client_profile" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_CLIENT_PROFILE', null),
                "associated_tenant" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_ASSOCIATED_TENANT', null),
                "reseller" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_RESELLER', null),
                "subreseller" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_SUBRESELLER', null),
                "currency" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_CURRENCY', 'USD'),
                "default_tax" => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_DEFAULT_TAX', null),
                // 'billing_profile' => env('FPBX_CGRT_DEFAULT_CLIENT_ADD_BILLING_PROFILE', 'Monthly'),
            ],
            'add_sipaccount' => [
                "register" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_REGISTER', false),
                "ip_address" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_IP_ADDRESS', null),
                "port" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_PORT', null),
                "codec1" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_CODEC1', null),
                "codec2" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_CODEC2', null),
                "codec3" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_CODEC3', null),
                "codec4" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_CODEC4', null),
                "bypass_media" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_BYPASS_MEDIA', null),
                "sip_force_contact" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_SIP_FORCE_CONTACT', null),
                "sip_force_expires" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_SIP_FORCE_EXPIRES', null),
                "call_timeout" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_CALL_TIMEOUT', 30),
                "callerid_select" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_CALLERID_SELECT', "pass_thru"),
                "caller_id_name" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_CALLER_ID_NAME', null),
                "caller_id_number" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_CALLER_ID_NUMBER', null),
                "set_privacy" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_SET_PRIVACY', false),
                "voicemail" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_VOICEMAIL', false),
                "voicemail_id" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_VOICEMAIL_ID', ""),
                "voicemail_uuid" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_VOICEMAIL_UUID', null),
                "voicemail_name_path" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_VOICEMAIL_NAME_PATH', ""),
                "voicemail_greeting_path" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_VOICEMAIL_GREETING_PATH', ""),
                "voicemail_password" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_VOICEMAIL_PASSWORD', null),
                "voicemail_email" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_VOICEMAIL_EMAIL', null),
                "voicemail_attach_file" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_VOICEMAIL_ATTACH_FILE', false),
                "voicemail_keep_after_email" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_VOICEMAIL_KEEP_AFTER_EMAIL', false),
                "custom_sip_headers" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_CUSTOM_SIP_HEADERS', ""),
                "description" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_DESCRIPTION', ""),
                "cli_debug" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_CLI_DEBUG', false),
                "tps_check" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_TPS_CHECK', false),
                "enabled" => env('FPBX_CGRT_DEFAULT_ADD_SIPACCOUNT_ENABLED', "True"),
            ],
            'tariffplan_assign' => [
                "tariffplan_name" => env('FPBX_CGRT_DEFAULT_TARIFFPLAN_ASSIGN_TARIFFPLAN_NAME', "GLOBAL_STANDARD"),
                // "routingplan_name" => env('FPBX_CGRT_DEFAULT_TARIFFPLAN_ASSIGN_ROUTINGPLAN_NAME', "DEFAULT"),
                "tech_prefix" => env('FPBX_CGRT_DEFAULT_TARIFFPLAN_ASSIGN_TECH_PREFIX', ""),
            ],
        ],
        'enabled' => env('FPBX_CGRT_ENABLED', false),
        'base_uri' => env('FPBX_CGRT_BASE_URI'),
        'username' => env('FPBX_CGRT_USERNAME'),
        'password' => env('FPBX_CGRT_PASSWORD'),
    ],
];
