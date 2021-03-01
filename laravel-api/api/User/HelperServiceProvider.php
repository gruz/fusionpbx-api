<?php

namespace Api\User;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
   
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // fuda:
        //    TODO: think about universal way to get real api path
        //          and user helpers path
        $path =  str_replace('infrastructure', 'api', app_path()) . '/User/Helpers/*.php';
        $helpers = glob($path);

        foreach ($helpers as $filename) {
            require_once($filename);
        }
    }

}
