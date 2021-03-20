<?php

namespace Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    
     /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        error_reporting(E_ALL & ~E_NOTICE);
    }
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
