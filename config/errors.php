<?php
return [

    /*
    |—————————————————————————————————————
    | Default Errors
    |—————————————————————————————————————
    */

    'Infrastructure\Auth\Exceptions\InvalidCredentialsException' => [
        'message' => 'Invalid credentials',
        'code' => '1000',
    ],
    'Api\Extensions\Exceptions\ExtensionNotFoundException' => [
        'message' => 'Extension not found',
        'code' => '1001',
    ],
    'Api\Extensions\Exceptions\InvalidExtensionException' => [
        'message' => 'The Extension with ID :extensionId is not an Extension',
        'code' => '1002',
    ],
    'Api\Users\Exceptions\UserNotFoundException' => [
        'message' => 'User not found',
        'code' => '1003',
    ],
    'Api\Extensions\Exceptions\ExtensionExistsException' => [
        'message' => 'Extension :extension for domain :domain_name already exists',
        'code' => '1004',
    ],
    'Api\Users\Exceptions\DomainExistsException' => [
        'message' => 'Domain already exists',
        'code' => '1005',
    ],
    'Api\Users\Exceptions\GroupNotFoundException' => [
        'message' => 'Group not found',
        'code' => '1006',
    ],
    'Api\Users\Exceptions\InvalidGroupException' => [
        'message' => 'The group with ID :groupId is not a group',
        'code' => '1007',
    ],
    'Api\Users\Exceptions\WrongSignupDataException' => [
        'message' => 'WrongSignupDataException',
        'code' => '1008',
    ],
    'Api\Users\Exceptions\DomainNotFoundException' => [
        'message' => 'Domain not found',
        'code' => '1009',
    ],
    'Api\Users\Exceptions\UserExistsException' => [
        'message' => 'User already exists',
        'code' => '1010',
    ],
    'Api\Users\Exceptions\EmailExistsException' => [
        'message' => 'Email already exists',
        'code' => '1011',
    ],
];