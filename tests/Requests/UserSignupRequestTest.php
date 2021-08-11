<?php

namespace Tests\Requests;

use Tests\TestCase;
use Gruz\FPBX\Models\Domain;
use Gruz\FPBX\Models\Extension;
use Tests\Traits\UserTrait;

class UserSignupRequestTest extends TestCase
{
    use UserTrait;

    private function getWorkingPassword() {
        return 'SomePassword12';
    }

    private function getWorkingVoicemailPassword() {
        return '1234';
    }

    public function testFailWhenUserExistsInADomain()
    {
        list($response, $email) = $this->simulateDomainSignupAndActivate();
        $data = $this->testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);

        $data['user_email'] = $email;
        $data['domain_name'] = $response->json('domain_name');
        $extension = Extension::max('extension');
        $data['extensions'] = [[
            'extension' => ++$extension, // Setting any non-exisiting number
            'password' => $this->getWorkingPassword(),
            "voicemail_password" => $this->getWorkingVoicemailPassword()
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
            'password' => $this->getWorkingPassword(),
            "voicemail_password" => $this->getWorkingVoicemailPassword()
        ]];

        $response = $this->json('post', route('fpbx.user.signup', $data));
        $response->assertStatus(422);
        $response->assertJsonFragment(['detail' => 'The extensions.0.extension has already been taken.']);
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

        $extension = $this->extensionService->getNewExtension($domain_uuid);

        $data['extensions'] = [[
            'extension' => $extension, // Setting any non-exisiting number
            'password' => $this->getWorkingPassword(),
            "voicemail_password" => $this->getWorkingVoicemailPassword()
        ]];

        $response = $this->json('post', route('fpbx.user.signup', $data));
        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected domain name is invalid."]
            ]
        ]);
    }

    public function testFailBadResellerCode()
    {
        config(['fpbx.resellerCode.required' => true]);
        $data = $this->testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);
        list($response, $email) = $this->simulateDomainSignupAndActivate();
        $domain_name = $response->json('domain_name');
        $domainModel = Domain::where('domain_name', $domain_name)->first();
        $domain_uuid = $domainModel->domain_uuid;

        $nonExistingEmail = $this->prepareNonExistingEmailInDomain($domain_uuid);

        $data['user_email'] = $nonExistingEmail;
        $data['domain_name'] = $domain_name;

        $extension = $this->extensionService->getNewExtension($domain_uuid);

        $data['extensions'] = [[
            'extension' => $extension, // Setting any non-exisiting number
            'password' => $this->getWorkingPassword(),
            "voicemail_password" => $this->getWorkingVoicemailPassword()
        ]];

        $response = $this->json('post', route('fpbx.user.signup', $data));
        $response->assertStatus(422);
        $response->assertJsonFragment(["detail" => "The reseller reference code field is required."]);

        $data['reseller_reference_code'] = uniqid();

        $response = $this->json('post', route('fpbx.user.signup', $data));
        $response->assertStatus(422);
        $response->assertJsonFragment(["detail" => "The selected reseller reference code is invalid."]);

    }

}
