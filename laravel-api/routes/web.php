<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$router->get('/test', [ \App\Http\Controllers\FrontController::class, 'test' ]);

$router->get('/docs/redoc', function(){
     return view('documenation.index');
 });

// return;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/refresh-captcha', [FrontController::class, 'refreshCaptcha']);
Route::get('lang/{locale}', [LocalizationController::class, 'lang'])->name('lang');


require __DIR__.'/auth.php';
