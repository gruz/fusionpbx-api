<?php

use Api\User\Models\User;
use Api\User\Models\Group_user;
use Illuminate\Support\Facades\Route;
use Api\User\Repositories\UserRepository;
use Api\Extension\Repositories\ExtensionRepository;
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
    $user = app(new ExtensionRepository)->getByID('734cd120-1ba7-11eb-a5e0-abc236f965be', ['includes'=>'users'])
        // ->has('domain')
        // ->with('groups')
        // ->has('permissions')
    ;
    dd($user);
});
