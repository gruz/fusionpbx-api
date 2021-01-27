<?php

$router->post('/logout', 'LoginController@logout');
$router->post('/forgot-password', 'LoginController@forgotPassword')
       ->middleware('guest')
       ->name('password.email');