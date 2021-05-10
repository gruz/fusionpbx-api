<?php

namespace Infrastructure\Providers;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Optimus\Api\System\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        parent::boot();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->booted(function () {
            $this->setRootControllerNamespace();

            if ($this->routesAreCached()) {
                $this->loadCachedRoutes();
            } else {
                $this->loadRoutes();

                $this->app->booted(function () {
                    $this->app['router']->getRoutes()->refreshNameLookups();
                    $this->app['router']->getRoutes()->refreshActionLookups();
                });
            }
        });
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

        $this->registerRoutesFromGeneratedJson();
    }

    private function registerRoutesFromGeneratedJson() {
        $filePath = 'swagger/routes.json';
        if (!Storage::exists($filePath)) {
            Artisan::call('l5-swagger:generate');
        }

        $swaggerRoutes = Storage::get($filePath);
        $swaggerRoutes = json_decode($swaggerRoutes);

        foreach ($swaggerRoutes as $route) {
            if ($this->checkRouteIsRegistered($route->path, $route->method)) {
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

        static $routes = null;

        if (empty($routes)) {
            $routes = \Route::getRoutes()->getRoutes();
        }

        foreach ($routes as
        /** @var \Route $r */
        $r) {
            $paths[$r->uri] = $r->methods;
        }
        // dd($route, $method, $paths);
        foreach ($routes as
        /** @var \Route $r */
        $r) {

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
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
