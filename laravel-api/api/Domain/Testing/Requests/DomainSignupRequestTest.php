<?php

namespace Api\Domain\Testing\Requests;

use Faker\Factory;
use Illuminate\Support\Arr;
use Api\Domain\Models\Domain;
use Infrastructure\Testing\TestCase;
use Infrastructure\Testing\TestRequestTrait;
use Infrastructure\Services\TestRequestFactoryService;

class DomainSignupRequestTest extends TestCase
{
    use TestRequestTrait;

    public function validationProvider()
    {
        // return [];
        // $this->withoutExceptionHandling();
        /* WithFaker trait doesn't work in the dataProvider */
        $faker = Factory::create(Factory::DEFAULT_LOCALE);

        $this->createApplication();
        /**
         * @var TestRequestFactoryService
         */
        $testRequestFactoryService = app(TestRequestFactoryService::class);
        $data = $testRequestFactoryService->makeDomainRequest();

        $systemDomainName = Domain::first()->getAttribute('domain_name');

        $return = [
            'fail_when_domain_exists' => [
                'passed' => true,
                'data' => array_merge($data, ['domain_name' => $systemDomainName]),// TODO get system domain from DB
            ],
            'pass_when_domain_not_exists' => [
                'passed' => true,
                'data' => $data
            ],
            'fail_if_not_a_valid_domain' => [
                'passed' => false,
                'data' => array_merge($data, ['domain_name' => $faker->sentence]),
            ],
            'fail_if_nousers' => [
                'passed' => false,
                'data' => array_merge($data, ['users' => []]),
            ],
        ];

        $dataModified = $this->makeDuplicatedUserEmails($data);
        $return['fail_if_same_mails'] = [
            'passed' => false,
            'data' => $dataModified,
        ];

        $dataModified = $this->removeEmails($data);
        $return['fail_no_user_email'] = [
            'passed' => false,
            'data' => $dataModified,
        ];

        $dataModified = $testRequestFactoryService->makeDomainRequest([
            'adminIsPresent' => false,
        ]);
        $return['fail_if_no_admin_among_user'] = [
            'passed' => false,
            'data' => $dataModified,
        ];

        return $return;
    }

    private function makeDuplicatedUserEmails($data) {
        Arr::set($data, 'users.0.user_email', 'a@a.com');
        Arr::set($data, 'users.1.user_email', 'a@a.com');

        return $data;
    }

    private function removeEmails($data) {
        Arr::forget($data, 'users.0.user_email');
        Arr::set($data, 'users.1.user_email', '');

        return $data;
    }
}
