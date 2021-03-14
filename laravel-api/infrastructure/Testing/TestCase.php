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

    protected function simulateSignup()
    {
        Notification::fake();

        PostponedAction::query()->truncate();
        $testRequestFactoryService = app(TestRequestFactoryService::class);
        $request = $testRequestFactoryService->makeDomainRequest();
        $response = $this->json('post', route('fpbx.post.domain.signup'), $request);

        $data = [$request, $response];

        return $data;
    }
}
