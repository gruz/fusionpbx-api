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

        if (PostponedAction::count() >= 2) {
            $second = PostponedAction::skip(1)->first();
            $second->created_at = Carbon::now()->subCentury();
            $second->save();
        } else {
            $second = $model->replicate();
            $second->hash = Str::uuid();
            $second->created_at = Carbon::now()->subCentury();
            $second->save();
        }

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

        return $return;
    }
}
