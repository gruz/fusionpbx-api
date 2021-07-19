<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\WebhookController;

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

Route::get('/test', [\App\Http\Controllers\FrontController::class, 'test']);

Route::get('/docs/redoc', function () {
    return view('documenation.index');
});


Route::get('/', function () {
    return view('welcome');
});

Route::get('/refresh-captcha', [\App\Http\Controllers\FrontController::class, 'refreshCaptcha']);
Route::get('lang/{locale}', [\App\Http\Controllers\LocalizationController::class, 'lang'])->name('lang');
Route::get('/prov', [\App\Http\Controllers\FrontController::class, 'getProvisioning']);


Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])->name('cashier.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/pay', [StripeController::class, 'payAmount'])->name('pay.amount');
    Route::get('/stripe-intent/{sum}', [StripeController::class, 'setupIntent'])->middleware(['auth', 'verified'])->name('stripe.intent');
});


require __DIR__ . '/auth.php';
