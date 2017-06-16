<?php

$router->get('/', 'DefaultApiController@index');

// ~ Route::post(['middleware' => 'auth:api'], function() {
    // ~ Route::resource('users', 'User\UserController');
	// ~ });

// ~ $router->post('register', '\Api\User\Controllers\UserController@register');
$router->post('/signup', '\Api\User\Controllers\UserController@signup');
$router->post('/activate/user/{}', '\Api\User\Controllers\UserController@activate');
// ~ $router->post('/create', '\Api\User\Controllers\UserController@create');
// ~ Route::resource('domain', '\Api\User\Controllers\DomainController');
// ~ Route::resource('user', '\Api\User\Controllers\UserController');