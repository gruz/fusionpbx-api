<?php

namespace Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MakeTestDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:maketest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will create a test database';

    /**
     * @var Process
     */
    protected $process;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $db_name = config('database.connections.pgsql.database') . '_test';
        \DB::statement('SELECT pg_terminate_backend (pid) FROM pg_stat_activity WHERE datname = \'' . $db_name . '\';');
        \DB::statement('DROP DATABASE IF EXISTS ' . $db_name . ';');
        \DB::statement('CREATE DATABASE ' . $db_name . ';');

        $this->call('db:backup');
        $this->call('db:restore', ['--dbname' => $db_name]);

        $directory = 'backups';
        $files = Storage::files($directory);
        sort($files);
        $path = last($files);
        Storage::delete($path);
   }
}