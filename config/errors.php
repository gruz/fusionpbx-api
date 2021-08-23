<?php
return [
    /*
    |—————————————————————————————————————
    | Default Errors
    |—————————————————————————————————————
    */

    \Gruz\FPBX\Exceptions\UserNotFoundException::class => [
        'message' => 'User not found',
        'code' => '1003',
    ],
    \Gruz\FPBX\Exceptions\DomainExistsException::class => [
        'message' => 'Domain already exists',
        'code' => '1005',
    ],
    \Gruz\FPBX\Exceptions\GroupNotFoundException::class => [
        'message' => 'Group not found',
        'code' => '1006',
    ],
    \Gruz\FPBX\Exceptions\InvalidGroupException::class => [
        'message' => 'The group with ID :groupId is not a group',
        'code' => '1007',
    ],
    \Gruz\FPBX\Exceptions\DomainNotFoundException::class => [
        'message' => 'Domain not found',
        'code' => '1009',
    ],
    \Gruz\FPBX\Exceptions\UserDisabledException::class => [
        'message' => 'User disabled',
        'code' => '1013',
    ],
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
    \Gruz\FPBX\Exceptions\InvalidStatusException::class => [
        'message' => 'Invalid status type passed: `:status` . Available statuses: `:available_statuses`',
        'code' => '1022',
    ],
    \Gruz\FPBX\Exceptions\InvalidServiceListException::class => [
        'message' => 'Invalid services list',
        'code' => '1023',
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
    \Gruz\FPBX\Exceptions\StatusNotFoundException::class => [
        'message' => 'User status record not found',
        'code' => '1030',
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
