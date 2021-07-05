<?php

namespace Tests;

use Storage;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Arr;
use Tests\CreatesApplication;
use App\Models\DefaultSetting;
use App\Models\PostponedAction;
use App\Services\TestHelperService;
use App\Services\Fpbx\ExtensionService;
use Illuminate\Support\Facades\Artisan;
use App\Services\TestRequestFactoryService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @var TestRequestFactoryService
     */
    public $testRequestFactoryService;

    /**
     * @var TestHelperService
     */
    protected $testHelperService;

    /**
     * @var ExtensionService
     */
    protected $extensionService;

    /**
     * @var Generator
     */
    public $faker;

    protected function setUp(): void
    {
        parent::setUp();
        // $this->withoutExceptionHandling();

        $this->testRequestFactoryService = app(TestRequestFactoryService::class);
        $this->testHelperService = app(TestHelperService::class);

        $this->faker = Factory::create(Factory::DEFAULT_LOCALE);
        $this->extensionService = app(ExtensionService::class);

        config(['fpbx.cgrt.enabled' => false]);
        config(['fpbx.resellerCode.required' => false]);
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

        if (config('fpbx.resellerCode.required')) {
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
