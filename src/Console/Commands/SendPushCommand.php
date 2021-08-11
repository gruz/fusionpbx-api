<?php

namespace Gruz\FPBX\Console\Commands;

use Illuminate\Console\Command;
use Gruz\FPBX\Services\PushService;

class SendPushCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:push {destination}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends push notiications';

    /**
     * The send push service
     *
     * @var PushService
     */
    protected $pushService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PushService $pushService)
    {
        parent::__construct();
        $this->pushService = $pushService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $destination = $this->argument('destination');
        $this->pushService->pushUser($destination);
    }
}
