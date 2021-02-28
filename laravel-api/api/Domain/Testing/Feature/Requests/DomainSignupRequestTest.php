<?php

namespace Api\Domain\Testing\Feature\Requests;

use Faker\Factory;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Infrastructure\Testing\TestCase;
use Api\Domain\Requests\DomainSignupRequest;
use Infrastructure\Testing\TestRequestTrait;
use Infrastructure\Services\TestRequestFactoryService;

class DomainSignupRequestTest extends TestCase
{
    use TestRequestTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = app()->get('validator');

        $this->rules = (new DomainSignupRequest())->rules();
    }

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

        $data = $testRequestFactoryService->makeDomainRequest([
            'adminIsPresent' => false,
        ]);

        $return['fail_if_no_admin_among_user'] = [
            'passed' => false,
            'data' => $data,
        ];

        return $return;
    }

    public function testRequestDomainNameIsTransformedIntoSubdomain() {

        $this->withoutExceptionHandling();
        /**
         * @var TestRequestFactoryService
         */
        $testRequestFactoryService = app(TestRequestFactoryService::class);

        $data = $testRequestFactoryService->makeDomainRequest();

        $data['domain_name'] = 'site.com';
        config(['fpbx.default.domain.mothership_domain' => 'default.com']);

        $data['is_subdomain'] = false;
        $request = new DomainSignupRequest($data);
        $this->assertEquals('site.com', $request->all()['domain_name']);

        $data['is_subdomain'] = true;
        $request = new DomainSignupRequest($data);
        $this->assertEquals('site.com.default.com', $request->all()['domain_name']);

        unset($data['is_subdomain']);

        config(['fpbx.default.domain.new_is_subdomain' => false]);
        $request = new DomainSignupRequest($data);
        $this->assertEquals('site.com', $request->all()['domain_name']);

        config(['fpbx.default.domain.new_is_subdomain' => true]);
        $this->assertEquals('site.com.default.com', $request->all()['domain_name']);
    }

    public function testDomainIsEnabledOrDisabledDependingOnRequestAndConfig() {

        $this->withoutExceptionHandling();
        /**
         * @var TestRequestFactoryService
         */
        $testRequestFactoryService = app(TestRequestFactoryService::class);

        $data = $testRequestFactoryService->makeDomainRequest();

        config(['fpbx.domain.enabled' => true]);

        $data['domain_enabled'] = true;
        $request = new DomainSignupRequest($data);
        $this->assertEquals(true, $request->all()['domain_enabled']);

        $data['domain_enabled'] = false;
        $request = new DomainSignupRequest($data);
        $this->assertEquals(false, $request->all()['domain_enabled']);


        config(['fpbx.domain.enabled' => false]);

        $data['domain_enabled'] = true;
        $request = new DomainSignupRequest($data);
        $this->assertEquals(false, $request->all()['domain_enabled']);

        $data['domain_enabled'] = false;
        $request = new DomainSignupRequest($data);
        $this->assertEquals(false, $request->all()['domain_enabled']);
    }

}
