<?php

namespace Infrastructure\Testing;

use Api\PostponedAction\Models\PostponedAction;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Notification;
use Infrastructure\Services\TestRequestFactoryService;

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
        PostponedAction::query()->truncate();
        Notification::fake();

        $data = $this->testRequestFactoryService->makeDomainRequest();
        $response = $this->json('post', route('fpbx.post.domain.signup'), $data);

        return [$data,  $response];
    }
}
