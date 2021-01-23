<?php

use Api\User\Controllers\UserController;
use Api\User\Controllers\DomainController;

$router->post('/signup/domain', [DomainController::class, 'signup']);
$router->post('/signup', [UserController::class, 'signup']);

$router->get('/user/activate/{hash}', [ UserController::class, 'activate']);


