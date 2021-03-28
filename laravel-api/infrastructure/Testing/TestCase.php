<?php

namespace Infrastructure\Testing;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Api\PostponedAction\Models\PostponedAction;
use Infrastructure\Services\TestRequestFactoryService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @var TestRequestFactoryService
     */
    public $testRequestFactoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testRequestFactoryService = app(TestRequestFactoryService::class);
    }

    protected function refreshDB() {
        Artisan::call('db:maketest');
        // Artisan::call('migrate:refresh');
    }

    protected function simulateSignup($forceNewRequestGeneration = true, $refreshDB = false, $request = [] )
    {
        if ($refreshDB) {
            $this->refreshDB();
        }

        // PostponedAction::query()->truncate();
        Notification::fake();

        /**
         * @var TestRequestFactoryService
         */
        $testRequestFactoryService = app(TestRequestFactoryService::class);
        if (empty($request)) {
            $request = $testRequestFactoryService->makeDomainRequest(['noCache' => $forceNewRequestGeneration]);
        }
        $response = $this->json('post', route('fpbx.post.domain.signup'), $request);

        $data = [$request, $response];

        return $data;
    }
}
