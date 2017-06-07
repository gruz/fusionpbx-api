<?php

$router->get('/', 'DefaultApiController@index');

// ~ Route::post(['middleware' => 'auth:api'], function() {
    // ~ Route::resource('users', 'Users\UsersController');
	// ~ });

// ~ $router->post('register', '\Api\Users\Controllers\UserController@register');
$router->post('/signup', '\Api\Users\Controllers\UserController@signup');
$router->post('/activate/user/{}', '\Api\Users\Controllers\UserController@activate');
// ~ $router->post('/create', '\Api\Users\Controllers\UserController@create');
// ~ Route::resource('domain', '\Api\Users\Controllers\DomainController');
// ~ Route::resource('user', '\Api\Users\Controllers\UserController');