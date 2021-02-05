<?php

use Api\User\Controllers\UserController;

$router->post('/signup', [UserController::class, 'signup']);

$router->get('/user/activate/{hash}', [ UserController::class, 'activate']);

$router->post('/reset-password', [
    'uses' => 'UserController@resetPassword',
    'as' => 'password.reset',
]);

$router->post('/forgot-password', [
       'uses' => 'UserController@forgotPassword',
       'as' => 'password.email',
]);

