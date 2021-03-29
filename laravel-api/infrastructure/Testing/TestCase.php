<?php

namespace Infrastructure\Testing;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Infrastructure\Services\TestRequestFactoryService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Storage;

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

        // // ##mygruz20210329030026  Disabled for now. Not sure if this makes sense
        // $this->saveResponseForSwagger('post', route('fpbx.post.domain.signup', [], false), $response);

        $data = [$request, $response];

        return $data;
    }

    private function saveResponseForSwagger($method, $path, $response) {

        $folderPath = 'swagger'. $path . '/' . $method . '/response/';
        $code = $response->baseResponse->getStatusCode();
        $mask = $folderPath . $code . '*' ;
        $mask = storage_path() . '/app/' . $mask;
        $folders = glob($mask);

        if (empty($folders)) {
            $folder = $folderPath . $code . ' ' . $response->baseResponse::$statusTexts[$code];
            Storage::makeDirectory($folder);
        } else {
            $folder = end($folders);
        }

        $file = $folder . '/'. $response->baseResponse::$statusTexts[$code] . '.json';

        if (!file_exists($file))  {
            $content = $response->baseResponse->getContent();
            file_put_contents($file, json_encode(json_decode($content), JSON_PRETTY_PRINT) );
        }

    }
}
