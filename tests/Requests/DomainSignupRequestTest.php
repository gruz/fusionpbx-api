<?php

namespace Tests\Requests;

use Faker\Factory;
use Illuminate\Support\Arr;
use App\Models\Domain;
use Tests\TestCase;
use App\Services\TestRequestFactoryService;

class DomainSignupRequestTest extends TestCase
{
    public function testFailWhenDomainExists()
    {
        $data = $this->testRequestFactoryService->makeDomainSignupRequest();
        $systemDomainName = Domain::first()->getAttribute('domain_name');

        $data['domain_name'] = $systemDomainName;

        $response = $this->json('post', route('fpbx.domain.signup', $data));

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The domain name has already been taken."]
            ]
        ]);

    }
    public function testFailWhenNoOrBadReferenceCode()
    {
        $data = $this->testRequestFactoryService->makeDomainSignupRequest();

        config(['fpbx.resellerCode.required' => true]);

        $response = $this->json('post', route('fpbx.domain.signup', $data));
        $response->assertStatus(422);
        $response->assertJsonFragment(["detail" => "The reseller reference code is required."]);

        $data['reseller_reference_code'] = uniqid();
        $response = $this->json('post', route('fpbx.domain.signup', $data));
        $response->assertStatus(422);
        $response->assertJsonFragment(["detail" => "The selected reseller reference code is invalid."]);
    }

    // public function testPassWhenDomainNotExists()
    // {
    //     $data = $this->testRequestFactoryService->makeDomainSignupRequest();
    //     $systemDomainName = Domain::first()->getAttribute('domain_name');

    //     $data['domain_name'] = $systemDomainName;

    //     $response = $this->json('post', route('fpbx.domain.signup', $data));

    //     $response->assertStatus(422);
    //     $response->assertJson([
    //         "errors" => [
    //             ["detail" => "The domain name has already been taken."]
    //         ]
    //     ]);
    // }


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
        $data = $testRequestFactoryService->makeDomainSignupRequest();

        $systemDomainName = Domain::first()->getAttribute('domain_name');

        $return = [
            'fail_when_domain_exists' => [
                'passed' => false,
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

        $dataModified = $testRequestFactoryService->makeDomainSignupRequest([
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
