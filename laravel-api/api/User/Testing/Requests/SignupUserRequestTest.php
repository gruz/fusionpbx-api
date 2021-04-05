<?php

namespace Api\User\Testing\Requests;

use Api\User\Models\User;
use Api\Domain\Models\Domain;
use Api\Extension\Models\Extension;
use Infrastructure\Testing\TestCase;

class SignupUserRequestTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testFailsIfDomainNotExists()
    {
        $nonExistingDomain = $this->faker->domainName;
        while (true) {
            $domain = Domain::where('domain_name', $nonExistingDomain)->first();
            if (empty($domain)) {
                break;
            } else {
                $nonExistingDomain = $this->faker->domainName;
            }
        }

        $data = $this->testRequestFactoryService->makeUserSignupRequest([
            'addDomainName' => true,
            // 'domain_name' => $systemDomainName,
        ]);

        $data['domain_name'] = $nonExistingDomain;

        $response = $this->json('post', route('fpbx.user.signup', $data));

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected domain name is invalid."]
            ]
        ]);
    }

    public function testFailWhenUserExistsInADomain() {
        list($response, $email) = $this->simulateDomainSignupAndActivate();
        $data = $this->testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);

        $data['user_email'] = $email;
        $data['domain_name'] = $response->json('domain_name');
        $extension = Extension::max('extension');
        $data['extensions'] = [[
            'extension' => ++$extension, // Setting any non-exisiting number
            'password' => 'somePass',
            "voicemail_password" => "956"
        ]];

        $response = $this->json('post', route('fpbx.user.signup', $data));

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The user email has already been taken."]
            ]
        ]);
    }

    public function testFailWhenUserExtensionAlreadyExists () {
            $data = $this->testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);
            list($response, $email) = $this->simulateDomainSignupAndActivate();
            $domain_uuid = Domain::where('domain_name', $response->json('domain_name'))->first()->domain_uuid;

            $nonExistingEmail = $this->faker->email;
            while (true) {
                $user = User::where('domain_uuid', $domain_uuid)
                    ->where('user_email', $nonExistingEmail)
                    ->first();
                if (empty($user)) {
                    break;
                } else {
                    $nonExistingEmail = $this->faker->email;
                }
            }

            $data['user_email'] = $nonExistingEmail;
            $data['domain_name'] = $response->json('domain_name');

            $extensions = Extension::where('domain_uuid', $domain_uuid)->get()->pluck('extension');

            $data['extensions'] = [[
                'extension' => $extensions[0], // Setting any non-exisiting number
                'password' => 'somePass',
                "voicemail_password" => "956"
            ]];

            $response = $this->json('post', route('fpbx.user.signup', $data));
            $response->assertStatus(422);
            $response->assertJson([
                "errors" => [
                    ["detail" => "The extensions.0.extension has already been taken."]
                ]
            ]);
    }
}
