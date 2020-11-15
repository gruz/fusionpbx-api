<?php

use Api\User\Models\Group_user;
use Api\User\Models\User;
use Illuminate\Support\Facades\Route;
$router->get('/', 'DefaultApiController@index');

// ~ Route::post(['middleware' => 'auth:api'], function() {
    // ~ Route::resource('users', 'User\UserController');
	// ~ });

// ~ $router->post('register', '\Api\User\Controllers\UserController@register');
$router->post('/signup', '\Api\User\Controllers\UserController@signup');
// ~ $router->post('/activate/user/{}', '\Api\User\Controllers\UserController@activate');
// ~ $router->post('/create', '\Api\User\Controllers\UserController@create');
// ~ Route::resource('domain', '\Api\User\Controllers\DomainController');
// ~ Route::resource('user', '\Api\User\Controllers\UserController');

// TOOD 8
// \Illuminate\Support\Facades\Auth::routes(['verify' => true]);

$router->get('/foo', function () {
    $user = Group_user::
        where('61ba1990-269e-11eb-a3c8-e1ee1cacdcd6')
        // ->has('domain')
        ->with('groups')
        // ->has('permissions')
    ;
    dd($user);
});
