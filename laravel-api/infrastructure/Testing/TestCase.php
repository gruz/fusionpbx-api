<?php

namespace Infrastructure\Testing;

use Illuminate\Support\Facades\Cache;
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

    protected function simulateSignup($noCache = false)
    {
        Notification::fake();

        PostponedAction::query()->truncate();
        /**
         * @var TestRequestFactoryService
         */
        $testRequestFactoryService = app(TestRequestFactoryService::class);
        $request = $testRequestFactoryService->makeDomainRequest(['noCache' => $noCache]);
        $response = $this->json('post', route('fpbx.post.domain.signup'), $request);

        $data = [$request, $response];

        return $data;
    }
}
