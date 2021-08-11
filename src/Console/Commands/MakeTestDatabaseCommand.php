<?php

namespace Gruz\FPBX\Console\Commands;

use Illuminate\Console\Command;
use Storage;
use Symfony\Component\Process\Process;

class MakeTestDatabaseCommand extends Command
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

        $this->createDatabaseIfNotExists($db_name);
        $this->removeAllTables();

        $this->call('db:backup');
        $this->call('db:restore', ['--dbname' => $db_name]);

        $directory = 'backups';
        $files = Storage::files($directory);
        sort($files);
        $path = last($files);
        Storage::delete($path);
    }

    private function createDatabaseIfNotExists($db_name)
    {
        // \DB::statement('SELECT pg_terminate_backend (pid) FROM pg_stat_activity WHERE datname = \'' . $db_name . '\';');
        // \DB::statement('DROP DATABASE IF EXISTS ' . $db_name . ';');
        // \DB::statement('CREATE DATABASE ' . $db_name . ';');

        // CREATE DATABASE IF NOT EXISTS
        $cmd = sprintf(
            'echo "SELECT \'CREATE DATABASE %s\' WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = \'%s\')\\gexec" | psql -U %s -h %s',
            $db_name,
            $db_name,
            config('database.connections.pgsql.username'),
            config('database.connections.pgsql.host')
        );

        $env = ['PGPASSWORD' => config('database.connections.pgsql.password')];
        $this->process = Process::fromShellCommandline($cmd, null, $env);
        $this->process->mustRun();
    }

    private function removeAllTables()
    {
        \DB::connection('pgsql_test')->statement("CREATE OR REPLACE FUNCTION drop_tables(username IN VARCHAR) RETURNS void AS $$
        DECLARE
            statements CURSOR FOR
                SELECT tablename FROM pg_tables
                WHERE tableowner = username AND schemaname = 'public';
        BEGIN
            FOR stmt IN statements LOOP
                EXECUTE 'TRUNCATE TABLE ' || quote_ident(stmt.tablename) || ' CASCADE;';
            END LOOP;
        END;
        $$ LANGUAGE plpgsql;");

        \DB::connection('pgsql_test')->statement("SELECT drop_tables('" . config('database.connections.pgsql.username') . "')");
    }
}
