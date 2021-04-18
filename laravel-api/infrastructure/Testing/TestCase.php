<?php

namespace Infrastructure\Testing;

use Storage;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Arr;
use Api\Settings\Models\DefaultSetting;
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

    /**
     * @var Generator
     */
    public $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testRequestFactoryService = app(TestRequestFactoryService::class);

        $this->faker = Factory::create(Factory::DEFAULT_LOCALE);
    }

    protected function refreshDB()
    {
        Artisan::call('db:maketest');
        // Artisan::call('migrate:refresh');
    }

    protected function simulateDomainSignup($forceNewRequestGeneration = true, $refreshDB = false, $request = [])
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
            $request = $testRequestFactoryService->makeDomainSignupRequest(['noCache' => $forceNewRequestGeneration]);
        }

        if (config('fpbx.resellerCodeRequired')) {
            $reseller_code = $this->createResellerCode();
            $request['reseller_reference_code'] = $reseller_code;
        }

        $response = $this->json('post', route('fpbx.domain.signup'), $request);

        // // ##mygruz20210329030026  Disabled for now. Not sure if this makes sense
        // $this->saveResponseForSwagger('post', route('fpbx.post.domain', [], false), $response);

        $data = [$request, $response];

        return $data;
    }

    protected function createResellerCode() {
        $faker = Factory::create(Factory::DEFAULT_LOCALE);;
        $reseller_code = $faker->word;
        $defaultSettingModel = new DefaultSetting;
        $defaultSettingModel->createResellerCodeRecord($reseller_code);

        return $reseller_code;
    }

    protected function simulateDomainSignupAndActivate() {
        $this->simulateDomainSignup();
        $model = PostponedAction::last();
        $emails = Arr::get($model->request, 'users');
        $emails = collect($emails)->pluck('user_email');
        $email = $emails[0];
        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash, 'email' => $email]));
        return [ $response, $email];
    }

    private function saveResponseForSwagger($method, $path, $response)
    {

        $folderPath = 'swagger' . $path . '/' . $method . '/response/';
        $code = $response->baseResponse->getStatusCode();
        $mask = $folderPath . $code . '*';
        $mask = storage_path() . '/app/' . $mask;
        $folders = glob($mask);

        if (empty($folders)) {
            $folder = $folderPath . $code . ' ' . $response->baseResponse::$statusTexts[$code];
            Storage::makeDirectory($folder);
        } else {
            $folder = end($folders);
        }

        $file = $folder . '/' . $response->baseResponse::$statusTexts[$code] . '.json';

        if (!file_exists($file)) {
            $content = $response->baseResponse->getContent();
            file_put_contents($file, json_encode(json_decode($content), JSON_PRETTY_PRINT));
        }
    }
}
