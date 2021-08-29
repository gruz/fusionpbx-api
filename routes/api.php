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

// $router->post('/pushtoken', [\Gruz\FPBX\Http\Controllers\Api\Api\PushtokenController::class, 'create']);
// $router->post('/status', [ \Gruz\FPBX\Http\Controllers\Api\Api\StatusController::class,  'setStatus']);

// protected
// Route::middleware(['auth:api'])->group(function () use ($router) {
//     $router->post('/logout', [\Gruz\FPBX\Auth\Controllers\LoginController::class, 'logout']);
// });

// $router->post('/api/login', ['uses' => 'LoginController@login',  'as' => 'login']);
// $router->post('/api/login/refresh', 'LoginController@refresh');


Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);

    return ['token' => $token->plainTextToken];
});
// Route::post('/logout', function (Request $request) {

//     // Revoke the token that was used to authenticate the current request...
//     $request->user()->currentAccessToken()->delete();

//     // Revoke a specific token...
//     $user->tokens()->where('id', $tokenId)->delete();
// });

use Gruz\FPBX\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'username' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('username', $request->username)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});
