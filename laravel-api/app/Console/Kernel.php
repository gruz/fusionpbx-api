<?php

namespace App\Console;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Console\Commands\SendPushCommand;
use Symfony\Component\Finder\Finder;
use App\Console\Commands\MakeExceptionCommand;
use App\Console\Commands\AddUserCommand;
use App\Console\Commands\BackUpDatabaseCommand;
use App\Console\Commands\RestoreDatabaseCommand;
use App\Console\Commands\MakeTestDatabaseCommand;
use Illuminate\Console\Scheduling\Schedule;
use App\SwaggerProcessors\LoadConstantsHelper;
use Illuminate\Console\Application as Artisan;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        AddUserCommand::class,
        MakeExceptionCommand::class,
        SendPushCommand::class,
        BackUpDatabaseCommand::class,
        RestoreDatabaseCommand::class,
        MakeTestDatabaseCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        new LoadConstantsHelper('/' . config('l5-swagger.defaults.routes.docs') . '/' . config('l5-swagger.documentations.default.paths.docs_json'));
        $this->load(__DIR__.'/Commands');

        require_once base_path('app/Console/routes.php');

        $config = config('optimus.components');

        foreach ($config['namespaces'] as $namespace => $path) {
            $subDirectories = glob(sprintf('%s%s*', $path, DIRECTORY_SEPARATOR), GLOB_ONLYDIR);

            $paths = [];

            foreach ($subDirectories as $componentRoot) {
                // $component = substr($componentRoot, strrpos($componentRoot, DIRECTORY_SEPARATOR) + 1);

                $commandDirectory = sprintf('%s%sConsole', $componentRoot, DIRECTORY_SEPARATOR);

                if (file_exists($commandDirectory)) {
                    $paths[] =  $commandDirectory;
                }
            }

            if (empty($paths)) {
                continue;
            }

            foreach ((new Finder)->in($paths)->files() as $command) {
                $command = $namespace.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($command->getPathname(), $path)
                );

                if (is_subclass_of($command, Command::class) &&
                    ! (new ReflectionClass($command))->isAbstract()) {
                    Artisan::starting(function ($artisan) use ($command) {
                        $artisan->resolve($command);
                    });
                }
            }
        }
    }
}
