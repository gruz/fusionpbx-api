<?php

use Api\User\Models\User;
use Api\Extension\Models\Extension;

$router->get('/', [\Infrastructure\Api\Controllers\DefaultApiController::class, 'index']);

// ~ Route::post(['middleware' => 'auth:api'], function() {
    // ~ Route::resource('users', 'User\UserController');
	// ~ });

$router->post('/domain/signup', [\Api\User\Controllers\UserController::class, 'signupDomain']);
$router->post('/user/signup', [\Api\User\Controllers\UserController::class, 'signupUser']);

// ~ $router->post('register', '\Api\User\Controllers\UserController@register');
// $router->post('/signup', [\Api\User\Controllers\UserController::class, 'signup']);
// ~ $router->post('/activate/user/{}', '\Api\User\Controllers\UserController@activate');
// ~ $router->post('/create', '\Api\User\Controllers\UserController@create');
// ~ Route::resource('domain', '\Api\User\Controllers\DomainController');
// ~ Route::resource('user', '\Api\User\Controllers\UserController');

// TOOD 8
// \Illuminate\Support\Facades\Auth::routes(['verify' => true]);
