<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [FrontController::class, 'test']);
Route::get('/refresh-captcha', [FrontController::class, 'refreshCaptcha']);
Route::get('/prov', [FrontController::class, 'getProvisioning']);

Route::get('lang/{locale}', [\App\Http\Controllers\LocalizationController::class, 'lang'])->name('lang');

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])->name('cashier.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $cloudID = optional(
            \Auth::user()
            ->domain
            ->domain_settings
            ->where('domain_setting_subcategory', 'acrobits_cloud_id')
            ->first()
        )->getAttribute('domain_setting_value');

        $cloudID = $cloudID ? $cloudID : env('DEFAULT_ACROBITS_CLOUDID', '*');

        return view('dashboard', compact('cloudID'));
    })->name('dashboard');

    Route::post('/pay', [StripeController::class, 'payAmount'])->name('pay.amount');
    Route::get('/stripe-intent/{sum}', [StripeController::class, 'setupIntent'])->middleware(['auth', 'verified'])->name('stripe.intent');
});


require __DIR__.'/auth.php';
