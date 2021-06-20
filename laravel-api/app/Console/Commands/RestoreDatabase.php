<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RestoreDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore {--file=} {--dbname=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will restore the database';

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
        $file = $this->option('file');
        $dbname = $this->option('dbname');
        $dbname = $dbname ? $dbname : config('database.connections.pgsql.database');

        $directory = 'backups';
        if (!$file) {
            $files = Storage::files($directory);
            sort($files);
            $path = last($files);
        } else {
            $path = $directory . '/' . $file;
        }

        if (!Storage::exists($path)) {
            logger()->error('restore exception', ['File not found . ', Storage::path($path)]);
            $this->error('File not found ' . Storage::path($path));
            return;
        }

        try {
            // \DB::statement('UPDATE pg_database set datallowconn = \'true\' where datname = \'' . $dbname . '\'');
            // \DB::statement('UPDATE pg_database set datallowconn = \'false\' where datname = \'' . $dbname . '\'');
            // \DB::statement('SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = \'' . $dbname . '\'');
            // \DB::statement('DROP DATABASE IF EXISTS ' . $dbname);
            // \DB::statement('CREATE DATABASE ' . $dbname);
            if ($dbname === config('database.connections.pgsql.database')) {
                \DB::statement('DROP SCHEMA public CASCADE');
                \DB::statement('CREATE SCHEMA public');
            }

            $cmd = sprintf(
                'psql -U %s -h %s %s < %s',
                config('database.connections.pgsql.username'),
                config('database.connections.pgsql.host'),
                $dbname,
                Storage::path($path)
            );

            $env = ['PGPASSWORD' => config('database.connections.pgsql.password')];


            $this->process = Process::fromShellCommandline($cmd, null, $env);

            // $this->process = new Process();

            // $this->call('migrate:fresh');
            $this->info('The restore has been started');
            $this->process->mustRun();
            $this->info('The restore has been proceed successfully.');
        } catch (ProcessFailedException $exception) {

            logger()->error('restore exception', compact('exception'));
            $this->error('The restore process has been failed.' . $exception->getMessage());
        }
    }
}
