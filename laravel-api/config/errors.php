<?php
return [

    /*
    |—————————————————————————————————————
    | Default Errors
    |—————————————————————————————————————
    */
      
    'App\Auth\Exceptions\InvalidCredentialsException' => [
        'message' => 'Invalid credentials', 
        'code' => '1000', 
    ],
    'Api\Extension\Exceptions\ExtensionNotFoundException' => [
        'message' => 'Extension not found', 
        'code' => '1001', 
    ],
    'Api\Extension\Exceptions\InvalidExtensionException' => [
        'message' => 'The Extension with ID :extensionId is not an Extension', 
        'code' => '1002', 
    ],
    'Api\User\Exceptions\UserNotFoundException' => [
        'message' => 'User not found', 
        'code' => '1003', 
    ],
    'Api\Extension\Exceptions\ExtensionExistsException' => [
        'message' => 'Extension :extension for domain :domain_name already exists', 
        'code' => '1004', 
    ],
    'Api\Domain\Exceptions\DomainExistsException' => [
        'message' => 'Domain already exists', 
        'code' => '1005', 
    ],
    'Api\User\Exceptions\GroupNotFoundException' => [
        'message' => 'Group not found', 
        'code' => '1006', 
    ],
    'Api\User\Exceptions\InvalidGroupException' => [
        'message' => 'The group with ID :groupId is not a group', 
        'code' => '1007', 
    ],
    'Api\User\Exceptions\WrongSignupDataException' => [
        'message' => 'WrongSignupDataException', 
        'code' => '1008', 
    ],
    'Api\Domain\Exceptions\DomainNotFoundException' => [
        'message' => 'Domain not found', 
        'code' => '1009', 
    ],
    'Api\User\Exceptions\UserExistsException' => [
        'message' => 'User already exists', 
        'code' => '1010', 
    ],
    'Api\User\Exceptions\EmailExistsException' => [
        'message' => 'Email already exists', 
        'code' => '1011', 
    ],
    \Api\Extension\Exceptions\InvalidExtensionUserException::class => [
        'message' => 'The extension with ID :extension_userId is not an extension', 
        'code' => '1012', 
    ],
    'Api\User\Exceptions\UserDisabledException' => [
        'message' => 'User disabled', 
        'code' => '1013', 
    ],
    'Api\User\Exceptions\ActivationHashWrongException' => [
        'message' => 'Wrong activation hash', 
        'code' => '1015', 
    ],
    'Api\Pushtoken\Exceptions\WrongPushtokenDataException' => [
        'message' => 'Wrong Pushtoken input data', 
        'code' => '1016', 
    ],
    'Api\Pushtoken\Exceptions\InvalidPushtokenTypeException' => [
        'message' => 'Invalid push token type. Must be <b>production</b> or <b>sandbox</b>', 
        'code' => '1017', 
    ],
    'Api\Pushtoken\Exceptions\InvalidPushtokenClassException' => [
        'message' => 'Invalid push token class. Must be <b>voip</b> or <b>text</b>', 
        'code' => '1018', 
    ],
    'Api\User\Exceptions\WrongDestinationException' => [
        'message' => 'Bad destination to call :desitnation', 
        'code' => '1019', 
    ],
    'Api\Dialplan\Exceptions\CouldNotInjectDialplanException' => [
        'message' => 'Could not symlink laravel-api dialplan to FusionPBX folder structure', 
        'code' => '1020', 
    ],
    'Api\Status\Exceptions\InvalidStatusOSException' => [
        'message' => 'Invalid OS type passed: :os', 
        'code' => '1021', 
    ],
    'Api\Status\Exceptions\InvalidStatusException' => [
        'message' => 'Invalid status type passed: `:status` . Available statuses: `:available_statuses`', 
        'code' => '1022', 
    ],
    'Api\Status\Exceptions\InvalidServiceListException' => [
        'message' => 'Invalid services list', 
        'code' => '1023', 
    ],
    'App\Exceptions\Socket\InvalidJSONInput' => [
        'message' => 'Invalid JSON data', 
        'code' => '1024', 
    ],
    'App\Exceptions\Socket\NoCommadException' => [
        'message' => 'Missing command', 
        'code' => '1025', 
    ],
    'App\Exceptions\Socket\TooManyConnectionAttempts' => [
        'message' => 'Too many connection attempts', 
        'code' => '1026', 
    ],
    'App\Exceptions\Socket\TooManyConnections' => [
        'message' => 'Too many connections', 
        'code' => '1027', 
    ],
    'App\Exceptions\Socket\TooManyMessages' => [
        'message' => 'Too many messages', 
        'code' => '1028', 
    ],
    'App\Exceptions\Socket\NeedToLoginFirst' => [
        'message' => 'Please, login first', 
        'code' => '1029', 
    ],
    'Api\Status\Exceptions\StatusNotFoundException' => [
        'message' => 'User status record not found', 
        'code' => '1030', 
    ],
    'Api\Status\Exceptions\WrongStatusDataException' => [
        'message' => 'Wrong status data', 
        'code' => '1031', 
    ],
    \Infrastructure\Exceptions\MissingDomainUuidException::class => [
        'message' => 'Missing domain UUID',
        'code' => 1032
    ]
];