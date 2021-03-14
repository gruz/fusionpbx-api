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

    protected function simulateSignup($force = false)
    {
        Notification::fake();

        if (!$force) {
            $model = PostponedAction::first();

            $response = null;
            if (!empty($model)) {
                $response = unserialize(Cache::store('file')->get($model->hash));
            }

            if (!empty($response)) {
                return [ $model->request, $response ];
            }
        }

        PostponedAction::query()->truncate();
        $request = $this->testRequestFactoryService->makeDomainRequest();
        $response = $this->json('post', route('fpbx.post.domain.signup'), $request);

        $model = PostponedAction::first();
        Cache::store('file')->set($model->hash, serialize($response));
        $data = [$request, $response];

        return $data;
    }
}
