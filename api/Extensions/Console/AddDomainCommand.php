<?php

namespace Api\Domains\Console;

use Api\Domains\Repositories\DomainRepository;
use Illuminate\Console\Command;

class AddDomainCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domains:add {domain_name} {domain_enabled} {domain_description} {domain_parent_uuid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds a new domain';

    /**
     * Domain repository to persist domain in database
     *
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * Create a new command instance.
     *
     * @param  DomainRepository  $domainRepository
     * @return void
     */
    public function __construct(DomainRepository $domainRepository)
    {
        parent::__construct();

        $this->domainRepository = $domainRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $domain = $this->domainRepository->create([
            'domain_name' => $this->argument('domain_name'),
            'domain_enabled' => $this->argument('domain_enabled'),
            'domain_description' => $this->argument('domain_description'),
            'domain_description' => $this->argument('domain_description'),
            'domain_parent_uuid' => $this->argument('domain_parent_uuid'),
        ]);

        $this->info(sprintf('A domain was created with ID %s', $domain->id));
    }
}