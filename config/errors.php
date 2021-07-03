<?php
return [
    /*
    |—————————————————————————————————————
    | Default Errors
    |—————————————————————————————————————
    */

    \App\Exceptions\UserNotFoundException::class => [
        'message' => 'User not found',
        'code' => '1003',
    ],
    \App\Exceptions\DomainExistsException::class => [
        'message' => 'Domain already exists',
        'code' => '1005',
    ],
    \App\Exceptions\GroupNotFoundException::class => [
        'message' => 'Group not found',
        'code' => '1006',
    ],
    \App\Exceptions\InvalidGroupException::class => [
        'message' => 'The group with ID :groupId is not a group',
        'code' => '1007',
    ],
    \App\Exceptions\DomainNotFoundException::class => [
        'message' => 'Domain not found',
        'code' => '1009',
    ],
    \App\Exceptions\UserDisabledException::class => [
        'message' => 'User disabled',
        'code' => '1013',
    ],
    \App\Exceptions\WrongPushtokenDataException::class => [
        'message' => 'Wrong Pushtoken input data',
        'code' => '1016',
    ],
    \App\Exceptions\InvalidPushtokenTypeException::class => [
        'message' => 'Invalid push token type. Must be <b>production</b> or <b>sandbox</b>',
        'code' => '1017',
    ],
    \App\Exceptions\InvalidPushtokenClassException::class => [
        'message' => 'Invalid push token class. Must be <b>voip</b> or <b>text</b>',
        'code' => '1018',
    ],
    \App\Exceptions\WrongDestinationException::class => [
        'message' => 'Bad destination to call :desitnation',
        'code' => '1019',
    ],
    \App\Exceptions\CouldNotInjectDialplanException::class => [
        'message' => 'Could not symlink laravel-api dialplan to FusionPBX folder structure',
        'code' => '1020',
    ],
    \App\Exceptions\InvalidStatusOSException::class => [
        'message' => 'Invalid OS type passed: :os',
        'code' => '1021',
    ],
    \App\Exceptions\InvalidStatusException::class => [
        'message' => 'Invalid status type passed: `:status` . Available statuses: `:available_statuses`',
        'code' => '1022',
    ],
    \App\Exceptions\InvalidServiceListException::class => [
        'message' => 'Invalid services list',
        'code' => '1023',
    ],
    \App\Exceptions\Socket\InvalidJSONInput::class => [
        'message' => 'Invalid JSON data',
        'code' => '1024',
    ],
    \App\Exceptions\Socket\NoCommadException::class => [
        'message' => 'Missing command',
        'code' => '1025',
    ],
    \App\Exceptions\Socket\TooManyConnectionAttempts::class => [
        'message' => 'Too many connection attempts',
        'code' => '1026',
    ],
    \App\Exceptions\Socket\TooManyConnections::class => [
        'message' => 'Too many connections',
        'code' => '1027',
    ],
    \App\Exceptions\Socket\TooManyMessages::class => [
        'message' => 'Too many messages',
        'code' => '1028',
    ],
    \App\Exceptions\Socket\NeedToLoginFirst::class => [
        'message' => 'Please, login first',
        'code' => '1029',
    ],
    \App\Exceptions\StatusNotFoundException::class => [
        'message' => 'User status record not found',
        'code' => '1030',
    ],
    \App\Exceptions\WrongStatusDataException::class => [
        'message' => 'Wrong status data',
        'code' => '1031',
    ],
    \App\Exceptions\MissingDomainUuidException::class => [
        'message' => 'Missing domain UUID',
        'code' => 1032
    ]
];
