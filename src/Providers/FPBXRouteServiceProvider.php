<?php

namespace Gruz\FPBX\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class FPBXRouteServiceProvider extends ServiceProvider
{
    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'Gruz\FPBX\\Http\\Controllers';

    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot()
    {
        // $this->routes(function () {
        //     Route::prefix('api')
        //         ->middleware('api')
        //         ->namespace($this->namespace)
        //         ->group(base_path('routes/api.php'));

        //     Route::middleware('web')
        //         ->namespace($this->namespace)
        //         ->group(base_path('routes/web.php'));
        // });

        $this->registerRoutesFromGeneratedJson();

        // parent::boot();
    }

    private function registerRoutesFromGeneratedJson()
    {
        $filePath = 'swagger/routes.json';
        if (!Storage::exists($filePath)) {
            Artisan::call('l5-swagger:generate');
        }

        $swaggerRoutes = Storage::get($filePath);
        $swaggerRoutes = json_decode($swaggerRoutes);

        if (empty($swaggerRoutes)) {
            return;
        }

        foreach ($swaggerRoutes as $route) {
            if ($this->checkRouteIsRegistered($route->prefix . '/' . $route->path, $route->method)) {
                continue;
            }

            if (empty($route->name)) {
                $name = 'fpbx.' . $route->method . str_replace('/', '.', $route->path);
                $name = preg_replace('/\.[{].*[}]/', '', $name);

                if ('fpbx.get.' === $name) {
                    $name = 'api.home';
                }
            } else {
                $name = $route->name;
            }

            $middlewares = empty($route->middlewares) ? [] : $route->middlewares;

            if (!isset($route->prefix)) {
                $route->prefix = null;
            }

            Route::middleware($middlewares)
                ->namespace($this->namespace)
                ->prefix($route->prefix)
                ->{$route->method}($route->path, [
                    'uses' => $route->controller . '@' . $route->action,
                    'as' => $name
                ]);
        }
    }

    private function checkRouteIsRegistered($route, $method = 'GET')
    {
        $method = strtoupper($method);
        if ($route[0] === "/" && '/' !== $route) {
            $route = substr($route, 1);
        }

        /**
         * @var Route[]
         */
        static $routes = null;

        if (empty($routes)) {
            $routes = Route::getRoutes()->getRoutes();
        }

        foreach ($routes as $r) {
            $paths[$r->uri] = $r->methods;
        }

        foreach ($routes as $r) {
            if ($r->uri === $route && in_array($method, $r->methods)) {
                return true;
            }
            if (isset($r->action['as'])) {
                if ($r->action['as'] == $route) {
                    return true;
                }
            }
        }

        return false;
    }
}
