<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\Infrastructure\Api\Controllers\DefaultApiController::class, 'index']);

// ~ Route::post(['middleware' => 'auth:api'], function() {
    // ~ Route::resource('users', 'User\UserController');
	// ~ });

// TOOD 8
// \Illuminate\Support\Facades\Auth::routes(['verify' => true]);

$router->namespace('\\Api\\User\\Controllers')->group(function ($router) {
    $router->post('/forgot-password', [
       'uses' => 'UserController@forgotPassword',
       'as' => 'password.email',
    ]);
 });