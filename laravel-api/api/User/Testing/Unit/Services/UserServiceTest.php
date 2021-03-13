<?php

use Infrastructure\Testing\TestCase;

class UserServiceTest extends TestCase
{
    // /**
    //  * @var TeamService
    //  */
    // private $teamService;

    // /**
    //  * @var TestRequestFactoryService
    //  */
    // private $testRequestFactoryService;

    protected function setUp(): void
    {
        parent::setUp();
        // $this->teamService = $this->app->make(TeamService::class);
        // $this->testRequestFactoryService = $this->app->make(TestRequestFactoryService::class);
    }

    public function testDomainDoesntExists()
    {
    }
    
    public function testUserAlreadyExists()
    {
    }
}
