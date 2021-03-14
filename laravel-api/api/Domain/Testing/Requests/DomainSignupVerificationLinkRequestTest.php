<?php

namespace Api\Domain\Testing\Requests;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Infrastructure\Testing\TestCase;
use Infrastructure\Testing\TestRequestTrait;
use Api\PostponedAction\Models\PostponedAction;
use Carbon\Carbon;
use Infrastructure\Services\TestRequestFactoryService;

class DomainSignupVerificationLinkRequestTest extends TestCase
{
    use TestRequestTrait;

    public function validationProvider()
    {
        $this->app = $this->createApplication();
        $this->testRequestFactoryService = app(TestRequestFactoryService::class);

        list($request, $response) = $this->simulateSignup();

        $model = PostponedAction::first();

        $second = $model->replicate();
        $second->hash = Str::uuid();
        $second->created_at = Carbon::now()->subCentury();
        $second->save();

        $hash = $model->hash;
        $emails = Arr::get($request, 'users');
        $emails = collect($emails)->pluck('user_email');
        $address = $emails[0];

        $return = [
            'no_hash_fails' => [
                'passed' => false,
                'data' => ['email' => $address],
            ],
            'no_email_fails' => [
                'passed' => false,
                'data' => ['hash' => $hash],
            ],
            'bad_hash_not_uuid_fails' => [
                'passed' => false,
                'data' => ['hash' => '1'],
            ],
            'bad_hash_not_found_fails' => [
                'passed' => false,
                'data' => ['hash' => Str::uuid()],
            ],
            'expired_link_fails' => [
                'passed' => false,
                'data' => [
                    'hash' => $second->hash,
                    'email' => $address,
                ],
            ],
            'ok' => [
                'passed' => true,
                'data' => [
                    'hash' => $model->hash,
                    'email' => $address,
                ],
            ],
        ];

        // Ugly way to pass route parameter request while testing
        // app() here doesn't work as it's recreated in testing flow erasing global data
        $GLOBALS['test.request.hash'] = $model->hash;

        return $return;
    }
}
