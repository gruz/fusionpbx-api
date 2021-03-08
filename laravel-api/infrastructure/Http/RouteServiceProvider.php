<?php

namespace Infrastructure\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Optimus\Api\System\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    private function checkRouteIsRegistered($route, $method = 'GET')
    {
        $method = strtoupper($method);
        if ($route[0] === "/" && '/' !== $route) {
            $route = substr($route, 1);
        }

        static $routes = null;

        if (empty($routes)) {
            $routes = \Route::getRoutes()->getRoutes();
        }

        foreach ($routes as /** @var \Route $r */ $r) {
            $paths[$r->uri] = $r->methods;
        }
        // dd($route, $method, $paths);
        foreach ($routes as /** @var \Route $r */ $r) {

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

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        parent::map($router);

        $swaggerRoutes = Storage::disk('local')->get('swagger/routes.json');
        $swaggerRoutes = json_decode($swaggerRoutes);

        foreach ($swaggerRoutes as $path => $route) {
            if ($this->checkRouteIsRegistered($path, $route->method)) {
                continue;
            }

            $name = 'fpbx.' . $route->method . str_replace('/', '.', $path);
            $name = preg_replace('/\.[{].*[}]/', '', $name);

            if ('fpbx.get.' === $name) {
                $name = 'api.home';
            }

            if ($route->auth) {
                $middlewares = ['auth:api'];
            } else {
                $middlewares = ['api'];
            }
            Route::middleware($middlewares)
                ->namespace($this->namespace)
                ->{$route->method}($path, [
                        'uses' => $route->controller . '@' . $route->action,
                        'as' => $name
                    ])
                ;
        }
    }
}
