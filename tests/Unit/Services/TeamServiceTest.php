<?php

namespace Tests\Unit\Services;

use Gruz\FPBX\Services\Fpbx\TeamService;
use Gruz\FPBX\Services\TestRequestFactoryService;
use Tests\TestCase;

class TeamServiceTest extends TestCase
{
    /**
     * @var TeamService
     */
    public $teamService;

    /**
     * @var TestRequestFactoryService
     */
    public $testRequestFactoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teamService = $this->app->make(TeamService::class);
        $this->testRequestFactoryService = $this->app->make(TestRequestFactoryService::class);
    }

    public function testPrepareSignupData()
    {
        $this->withoutExceptionHandling();

        $data = $this->testRequestFactoryService->makeDomainSignupRequest();

        $data['domain_name'] = 'site.com';
        config(['fpbx.default.domain.mothership_domain' => 'default.com']);

        $data['is_subdomain'] = false;

        $this->assertEquals('site.com', $this->teamService->prepareData($data)['domain_name']);

        $data['is_subdomain'] = true;
        $this->assertEquals('site.com.default.com', $this->teamService->prepareData($data)['domain_name']);

        unset($data['is_subdomain']);

        config(['fpbx.default.domain.new_is_subdomain' => false]);
        $this->assertEquals('site.com', $this->teamService->prepareData($data)['domain_name']);

        config(['fpbx.default.domain.new_is_subdomain' => true]);
        $this->assertEquals('site.com.default.com', $this->teamService->prepareData($data)['domain_name']);
    }

    public function testDomainIsEnabledOrDisabledDependingOnRequestAndConfig()
    {
        $this->withoutExceptionHandling();

        $data = $this->testRequestFactoryService->makeDomainSignupRequest();

        config(['fpbx.default.domain.enabled' => true]);

        $data['domain_enabled'] = true;
        $this->assertEquals(true, $this->teamService->prepareData($data)['domain_enabled']);

        $data['domain_enabled'] = false;
        $this->assertEquals(false, $this->teamService->prepareData($data)['domain_enabled']);

        config(['fpbx.default.domain.enabled' => false]);

        $data['domain_enabled'] = true;
        $this->assertEquals(false, $this->teamService->prepareData($data)['domain_enabled']);

        $data['domain_enabled'] = false;
        $this->assertEquals(false, $this->teamService->prepareData($data)['domain_enabled']);
    }
}
