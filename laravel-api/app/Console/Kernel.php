<?php

namespace App\Console;

use App\Console\Commands\AddUserCommand;
use App\Console\Commands\SendPushCommand;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\MakeExceptionCommand;
use App\SwaggerProcessors\LoadConstantsHelper;
use App\Console\Commands\BackUpDatabaseCommand;
use App\Console\Commands\RestoreDatabaseCommand;
use App\Console\Commands\MakeTestDatabaseCommand;
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

        require base_path('routes/console.php');

        return;
    }
}
