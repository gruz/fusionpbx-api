<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackUpDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will backup the database';

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

        $path = 'backups';
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);
        }

        $cmd = sprintf(
            'pg_dump -U %s -h %s %s -f %s',
            config('database.connections.pgsql.username'),
            config('database.connections.pgsql.host'),
            config('database.connections.pgsql.database'),
            Storage::path($path) . '/' . sprintf('backup_%s.sql', now()->format('YmdHis'))
        );

        $env = [ 'PGPASSWORD' => config('database.connections.pgsql.password')];

        $this->process = Process::fromShellCommandline($cmd, null, $env);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->info('The backup has been started');
            $this->process->mustRun();
            $this->info('The backup has been proceed successfully.');
        } catch (ProcessFailedException $exception) {
            dd($exception->getMessage());
            logger()->error('Backup exception', compact('exception'));
            $this->error('The backup process has been failed.');
        }
    }
}