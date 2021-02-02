<?php

$router->post('/login', 'LoginController@login');
$router->post('/login/refresh', 'LoginController@refresh');

$router->post('/reset-password', [
    'uses' => 'LoginController@resetPassword',
    'as' => 'password.reset',
]);

// $router->get('/forgot-password',
//               function () { 
//                      return view('auth.forgot-password');
//                })
//        ->name('password.request');

$router->post('/forgot-password', [
       'uses' => 'LoginController@forgotPassword',
       'as' => 'password.email',
]);
