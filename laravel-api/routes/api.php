<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

$router = app(Router::class);   

$router->get('/extensions', [\App\Http\Controllers\Api\ExtensionController::class,'getAll']);
$router->get('/extension/{id}', [\App\Http\Controllers\Api\ExtensionController::class,'getById']);
$router->post('/extension', [\App\Http\Controllers\Api\ExtensionController::class,'create']);
$router->put('/extension/{id}', [\App\Http\Controllers\Api\ExtensionController::class,'update']);
$router->delete('/extension/{id}', [\App\Http\Controllers\Api\ExtensionController::class,'delete']);

$router->post('/pushtoken', [\App\Http\Controllers\Api\PushtokenController::class, 'create']);

$router->post('/status', [ \App\Http\Controllers\Api\StatusController::class,  'setStatus']);

// protected
Route::middleware(['auth:api'])->group(function () use ($router) {
    $router->post('/logout', [\App\Auth\Controllers\LoginController::class, 'logout']); 
});

// $router->post('/api/login', ['uses' => 'LoginController@login',  'as' => 'login']);
// $router->post('/api/login/refresh', 'LoginController@refresh');
