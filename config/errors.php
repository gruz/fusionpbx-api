<?php
return [
    /*
    |—————————————————————————————————————
    | Default Errors
    |—————————————————————————————————————
    */

    \Gruz\FPBX\Exceptions\WrongPushtokenDataException::class => [
        'message' => 'Wrong Pushtoken input data',
        'code' => '1016',
    ],
    \Gruz\FPBX\Exceptions\InvalidPushtokenTypeException::class => [
        'message' => 'Invalid push token type. Must be <b>production</b> or <b>sandbox</b>',
        'code' => '1017',
    ],
    \Gruz\FPBX\Exceptions\InvalidPushtokenClassException::class => [
        'message' => 'Invalid push token class. Must be <b>voip</b> or <b>text</b>',
        'code' => '1018',
    ],
    \Gruz\FPBX\Exceptions\WrongDestinationException::class => [
        'message' => 'Bad destination to call :desitnation',
        'code' => '1019',
    ],
   \Gruz\FPBX\Exceptions\InvalidStatusOSException::class => [
        'message' => 'Invalid OS type passed: :os',
        'code' => '1021',
    ],
    \Gruz\FPBX\Exceptions\Socket\InvalidJSONInput::class => [
        'message' => 'Invalid JSON data',
        'code' => '1024',
    ],
    \Gruz\FPBX\Exceptions\Socket\NoCommadException::class => [
        'message' => 'Missing command',
        'code' => '1025',
    ],
    \Gruz\FPBX\Exceptions\Socket\TooManyConnectionAttempts::class => [
        'message' => 'Too many connection attempts',
        'code' => '1026',
    ],
    \Gruz\FPBX\Exceptions\Socket\TooManyConnections::class => [
        'message' => 'Too many connections',
        'code' => '1027',
    ],
    \Gruz\FPBX\Exceptions\Socket\TooManyMessages::class => [
        'message' => 'Too many messages',
        'code' => '1028',
    ],
    \Gruz\FPBX\Exceptions\Socket\NeedToLoginFirst::class => [
        'message' => 'Please, login first',
        'code' => '1029',
    ],
    \Gruz\FPBX\Exceptions\WrongStatusDataException::class => [
        'message' => 'Wrong status data',
        'code' => '1031',
    ],
    \Gruz\FPBX\Exceptions\MissingDomainUuidException::class => [
        'message' => 'Missing domain UUID',
        'code' => 1032
    ]
];
