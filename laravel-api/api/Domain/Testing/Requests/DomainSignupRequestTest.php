<?php

namespace Api\Domain\Testing\Requests;

use Faker\Factory;
use Api\User\Models\User;
use Illuminate\Support\Arr;
use Infrastructure\Testing\TestCase;
use Infrastructure\Testing\TestRequestTrait;
use Infrastructure\Services\TestRequestFactoryService;

class DomainSignupRequestTest extends TestCase
{
    use TestRequestTrait;

    public function validationProvider()
    {
        // $this->withoutExceptionHandling();
        /* WithFaker trait doesn't work in the dataProvider */
        $faker = Factory::create(Factory::DEFAULT_LOCALE);

        $this->createApplication();
        /**
         * @var TestRequestFactoryService
         */
        $testRequestFactoryService = app(TestRequestFactoryService::class);
        $data = $testRequestFactoryService->makeDomainRequest();

        $return = [
            'fail_when_domain_exists' => [
                'passed' => false,
                'data' => array_merge($data, ['domain_name' => '192.168.0.160']),
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

        $dataModified = $data;
        Arr::set($dataModified, 'users.0.user_email', 'a@a.com');
        Arr::set($dataModified, 'users.1.user_email', 'a@a.com');
        $return['fail_if_same_mails'] = [
            'passed' => false,
            'data' => $dataModified,
        ];

        $dataModified = $data;
        Arr::forget($dataModified, 'users.0.user_email');
        Arr::set($dataModified, 'users.1.user_email', '');
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


        $userData = Arr::get($data, 'users.0');
        $userData['username'] = 'aaaa';
        $userData['user_email'] = 'aaaa';
        $user = User::create($userData);

        $return['fail_if_user_exists'] = [
            'passed' => true,
            'data' => $data,
        ];
        // $user->delete();

        return $return;
    }
}
