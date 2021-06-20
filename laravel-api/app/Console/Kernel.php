<?php

namespace App\Console;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Api\User\Console\AddUserCommand;
use Symfony\Component\Finder\Finder;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\SendPush;
use Illuminate\Console\Application as Artisan;
use App\Console\Commands\MakeException;
use App\Console\Commands\BackUpDatabase;
use App\Console\Commands\RestoreDatabase;
use App\Console\Commands\MakeTestDatabase;
use App\SwaggerProcessors\LoadConstantsHelper;
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
        MakeException::class,
        SendPush::class,
        BackUpDatabase::class,
        RestoreDatabase::class,
        MakeTestDatabase::class,
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

        require_once base_path('infrastructure/Console/routes.php');

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