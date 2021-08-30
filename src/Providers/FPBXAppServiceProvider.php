<?php

namespace Gruz\FPBX\Providers;

use Illuminate\Support\Arr;
use Gruz\FPBX\Services\CGRTService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Gruz\FPBX\Http\Middleware\Localization;
use Gruz\FPBX\Http\Middleware\CheckApiToken;
use Gruz\FPBX\Console\Commands\AddUserCommand;
use Gruz\FPBX\Console\Commands\SendPushCommand;
use Gruz\FPBX\Console\Commands\InstallFPBXPackage;
use Gruz\FPBX\SwaggerProcessors\LoadConstantsHelper;
use Gruz\FPBX\Console\Commands\BackUpDatabaseCommand;
use Gruz\FPBX\Http\Middleware\L5SwaggerLoadConstants;
use Gruz\FPBX\Http\Middleware\LowercaseRequestParams;
use Gruz\FPBX\Console\Commands\RestoreDatabaseCommand;
use Gruz\FPBX\Console\Commands\MakeTestDatabaseCommand;

class FPBXAppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // \Laravel\Sanctum\Sanctum::ignoreMigrations();
        $this->app->register(\Optimus\ApiConsumer\Provider\LaravelServiceProvider::class);
        $this->app->register(\Optimus\Heimdal\Provider\LaravelServiceProvider::class);
        $this->app->bind(\App\Exceptions\Handler::class, \Gruz\FPBX\Exceptions\Handler::class);
        $this->app->bind(\App\Http\Middleware\Authenticate::class, \Gruz\FPBX\Http\Middleware\Authenticate::class);
        $this->app->bind(\App\Models\User::class, \Gruz\FPBX\Models\User::class);
        $this->registerHelpers();
        $this->loadConfig();
        $this->registerCGRT();

        // $this->app->register(Askedio\LaravelRatchet\Providers\LaravelRatchetServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the command if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallFPBXPackage::class,
                AddUserCommand::class,
                BackUpDatabaseCommand::class,
                MakeTestDatabaseCommand::class,
                RestoreDatabaseCommand::class,
                SendPushCommand::class,
            ]);

            $path = __DIR__ . '/../../config/';
            $this->publishes([
                $path. 'config.php' => config_path('fpbx.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/fpbx'),
            ], 'views');

            new LoadConstantsHelper('/' . config('l5-swagger.defaults.routes.docs') . '/' . config('l5-swagger.documentations.default.paths.docs_json'));
        }

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadJsonTranslationsFrom(__DIR__.'/../../resources/lang');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'fpbx');

        /**
         * @var \Illuminate\Foundation\Http\Kernel
         */
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(L5SwaggerLoadConstants::class);
        $kernel->pushMiddleware(LowercaseRequestParams::class);
        $kernel->appendMiddlewareToGroup('api', CheckApiToken::class);
        $kernel->appendMiddlewareToGroup('api', Localization::class);
        $kernel->appendMiddlewareToGroup('api', \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'fpbx');

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        config(['domain_enabled_field_type' => app(\Gruz\FPBX\Models\Domain::class)->getTableColumnsInfo(true)['domain_enabled']->getType()->getName()]);

        config(['app.url' => request()->getSchemeAndHttpHost()]);
    }

    private function loadConfig()
    {
        $path = __DIR__ . '/../../config/';
        $this->mergeConfigFrom($path. 'config.php', 'fpbx');

        $config = $this->app->make('config');

        $files = File::files($path);
        foreach ($files as $key => $file) {
            if ($file->getFilename() === 'config.php') {
                continue;
            }

            $key = basename($file->getFilename(), '.php');

            if ($key === "optimus.heimdal") {
                $config->set($key . '.formatters', []);
            }

            $mergeConfigArray = require $path. $file->getFilename();
            $newConfig = Arr::dot($mergeConfigArray);

            foreach ($newConfig as $newKey => $value) {
                $config->set($key . '.' . $newKey, $value);
            }

        }
    }

    private function registerCGRT()
    {
        if (config('fpbx.cgrt.enabled')) {
            $this->app->singleton(CGRTService::class, function ($app) {
                $base_url = config('fpbx.cgrt.base_uri');
                $username = config('fpbx.cgrt.username');
                $password = config('fpbx.cgrt.password');

                return new CGRTService($base_url, $username, $password);
            });
        }
    }

    private function registerHelpers()
    {
        $path = __DIR__ . '/../Helpers/*.php';
        $helpers = glob($path);

        foreach ($helpers as $filename) {
            if (strtolower(basename($filename)) === basename($filename)) {
                require_once($filename);
            }
        }
    }
}
