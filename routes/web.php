<?php

use App\Http\Controllers\StripeController;
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

$router->get('/test', [\App\Http\Controllers\FrontController::class, 'test']);

$router->get('/docs/redoc', function () {
    return view('documenation.index');
});

// return;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    /**
     * @var User
     */
    $user = auth()->user();
    $intent = $user->createSetupIntent();

    return view('dashboard', compact('intent'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/refresh-captcha', [\App\Http\Controllers\FrontController::class, 'refreshCaptcha']);
Route::get('lang/{locale}', [\App\Http\Controllers\LocalizationController::class, 'lang'])->name('lang');
Route::get('/prov', [\App\Http\Controllers\FrontController::class, 'getProvisioning']);


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/
Route::get('/stripe-payment', [StripeController::class, 'handleGet']);
Route::post('/stripe-payment', [StripeController::class, 'handlePost'])->name('stripe.payment');

// Route::get('/pay', [StripeController::class, 'show']);
Route::post('/pay', [StripeController::class, 'payAmount'])->name('pay.amount');

require __DIR__ . '/auth.php';
