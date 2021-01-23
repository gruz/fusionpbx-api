<?php

namespace Infrastructure\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        DB::listen(function ($query) {

            $bindings = $query->bindings;
            // Format binding data for sql insertion
            foreach ($bindings as $i => $binding) {
                if ($binding instanceof \DateTime) {
                    $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                } else if (is_string($binding)) {
                    $bindings[$i] = "'$binding'";
                }
            }

            // Insert bindings into query
            $query = str_replace(array('%', '?'), array('%%', '%s'), $query->sql);
            $query = vsprintf($query, $bindings);

            Log::info($query);
        });
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->fixPBXRootPath();
        $path = app_path() . '/Helpers/*.php';
        $helpers = glob($path);

        foreach ($helpers as $filename) {
            require_once($filename);
        }
    }

    private function fixPBXRootPath() {
        $rootFPBXPath = config('app.fpath_document_root');

        if (!realpath($rootFPBXPath)) {
            $rootFPBXPath = realpath(base_path($rootFPBXPath));
        }

        config(['app.fpath_document_root' => $rootFPBXPath]);
        $rootFPBXPath = config('app.fpath_document_root');
    }
}
