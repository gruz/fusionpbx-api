<?php

namespace Infrastructure\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Services\CGRTService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CGRTService::class, function ($app) {
            $base_url = config('fpbx.cgrt.base_uri');
            $username = config('fpbx.cgrt.username');
            $password = config('fpbx.cgrt.password');

            return new CGRTService($base_url, $username, $password);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }
        View::share('fieldClass', 'rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 ');
        View::share('langs', ['en', 'es', 'uk', 'ru']);
        config(['domain_enabled_field_type' => app(\Api\Domain\Models\Domain::class)->getTableColumnsInfo(true)['domain_enabled']->getType()->getName()]);
    }
}
