<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    |
    | Available statuses with priorities as array keys.
    | The keys are used to determine which status is the most available
    | E.g. there are several user devices online - busy, away, online, away.
    * Since the lowest number is 0 (online) whole user status would be online.
    |
    */
    'statuses' =>
    [
      'online' => '0',
      'away' => '100',
      'busy' => '200',
      'offline' => '1000',
    ],

    /*
    |--------------------------------------------------------------------------
    | OS types
    |--------------------------------------------------------------------------
    |
    | Just a list of OS types for API status table
    |
    */
    'OSes' =>
    [
      'ios',
      'android',
      'windows',
      'linux',
      'web',
      'other',
    ],

    /*
    |--------------------------------------------------------------------------
    | App services
    |--------------------------------------------------------------------------
    |
    | List of available services
    |
    */
    'services' =>
    [
      'voip',
      'chat',
    ],

    'status_lifetime' => 1000,

];
