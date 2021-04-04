<?php

namespace Api\User\Testing\Requests;

use Faker\Factory;
use Illuminate\Support\Arr;
use Api\Domain\Models\Domain;
use Api\Extension\Models\Extension;
use Infrastructure\Testing\TestCase;
use Infrastructure\Testing\TestRequestTrait;
use Api\PostponedAction\Models\PostponedAction;
use Infrastructure\Services\TestRequestFactoryService;

class SignupUserRequestTest extends TestCase
{
    use TestRequestTrait;

    public function validationProvider()
    {

        // return [];
        // $this->withoutExceptionHandling();
        /* WithFaker trait doesn't work in the dataProvider */
        $faker = Factory::create(Factory::DEFAULT_LOCALE);

        $this->app = $this->createApplication();
        $systemDomainName = Domain::first()->getAttribute('domain_name');
        /**
         * @var TestRequestFactoryService
         */
        $testRequestFactoryService = app(TestRequestFactoryService::class);
        $data = $testRequestFactoryService->makeUserSignupRequest([
            'addDomainName' => true,
            'domain_name' => $systemDomainName,
        ]);

        $return = [
            'fail_when_domain_not_exists' => [
                'passed' => false,
                'data' => $data,
            ],
            'fail_when_user_exists_in_a_domain' => [
                'passed' => false,
                'data' => function() use($testRequestFactoryService){
                    $data = $testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);
                    list($response, $email) = $this->simulateDomainSignupAndActivate();
                    $data['user_email'] = $email;
                    $data['domain_name'] = $response->json('domain_name');
                    $extension = Extension::max('extension');
                    $data['extensions'] = [[
                        'extension' => ++$extension, // Setting any non-exisiting number
                        'password' => 'somePass',
                        "voicemail_password" => "956"
                    ]];
                    return $data;
                },
            ],
            'fail_when_user_extension_already_exists' => [
                'passed' => false,
                'data' => function() use($testRequestFactoryService){
                    $data = $testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);
                    list($response, $email) = $this->simulateDomainSignupAndActivate();
                    $data['user_email'] = $email . 'a'; // To be sure it's an emails doesn't yet exists
                    $data['domain_name'] = $response->json('domain_name');
                    $domain_uuid = Domain::where('domain_name', $data['domain_name'])->first()->domain_uuid;
                    $extensions = Extension::where('domain_uuid', $domain_uuid)->get()->pluck('extension');

                    $data['extensions'] = [[
                        'extension' => $extensions[0], // Setting any non-exisiting number
                        'password' => 'somePass',
                        "voicemail_password" => "956"
                    ]];
                    return $data;
                },
            ],
            'pass_valid_data' => [
                'passed' => true,
                'data' => function() use($testRequestFactoryService){
                    $data = $testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);
                    list($response, $email) = $this->simulateDomainSignupAndActivate();
                    // $data['user_email'] = $email;
                    $data['domain_name'] = $response->json('domain_name');
                    $extension = Extension::max('extension');
                    $data['extensions'] = [[
                        'extension' => ++$extension, // Setting any non-exisiting number
                        'password' => 'somePass',
                        "voicemail_password" => "956"
                    ]];
                    return $data;
                },
            ],
        ];

        return $return;
    }
}
