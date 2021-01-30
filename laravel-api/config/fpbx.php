<?php

return [

    'domain' => [
        'enabled' => env('FPBX_DOMAIN_ENABLED', true),
        'description' => env('FPBX_DOMAIN_DESCRIPTION', 'Created via api at ' . date( 'Y-m-d H:i:s', time() )),
    ],

];
