<?php

namespace Api\User\Testing\Requests;

use Api\Domain\Models\Domain;
use Api\Extension\Models\Extension;
use Infrastructure\Testing\TestCase;
use Infrastructure\Testing\UserTrait;

class UserSignupRequestTest extends TestCase
{
    use UserTrait;

    public function testFailWhenUserExistsInADomain()
    {
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

    public function testFailWhenUserExtensionAlreadyExists()
    {
        $data = $this->testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);
        list($response, $email) = $this->simulateDomainSignupAndActivate();
        $domain_uuid = Domain::where('domain_name', $response->json('domain_name'))->first()->domain_uuid;

        $nonExistingEmail = $this->prepareNonExistingEmailInDomain($domain_uuid);

        $data['user_email'] = $nonExistingEmail;
        $data['domain_name'] = $response->json('domain_name');

        $extensions = Extension::where('domain_uuid', $domain_uuid)->get()->pluck('extension');

        $data['extensions'] = [[
            'extension' => $extensions[0], // Setting an exisiting number
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

    public function testFailWhenDomainDisabled()
    {
        $data = $this->testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);
        list($response, $email) = $this->simulateDomainSignupAndActivate();
        $domainModel = Domain::where('domain_name', $response->json('domain_name'))->first();
        $domainModel->domain_enabled = false;
        $domainModel->save();
        $domain_uuid = $domainModel->domain_uuid;

        $nonExistingEmail = $this->prepareNonExistingEmailInDomain($domain_uuid);

        $data['user_email'] = $nonExistingEmail;
        $data['domain_name'] = $response->json('domain_name');

        $extension = Extension::where('domain_uuid', $domain_uuid)->max('extension');

        $data['extensions'] = [[
            'extension' => ++$extension, // Setting any non-exisiting number
            'password' => 'somePass',
            "voicemail_password" => "956"
        ]];

        $response = $this->json('post', route('fpbx.user.signup', $data));
        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected domain name is invalid."]
            ]
        ]);
    }
}
