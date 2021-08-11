<?php

namespace Tests\Requests;

use Tests\TestCase;
use Gruz\FPBX\Models\Domain;
use Tests\Traits\UserTrait;

class UserLoginRequestTest extends TestCase
{
    use UserTrait;

    public function testFailLoginWhenDomainDisabledOrDoesNotExists()
    {
        $data = $this->testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);
        list($response, $email) = $this->simulateDomainSignupAndActivate();
        $domainModel = Domain::where('domain_name', $response->json('domain_name'))->first();
        $domainModel->domain_enabled = false;
        $domainModel->save();

        $data = [
            'username' => $data['username'],
            'password' => $this->faker->password(),
            "domain_name" => $domainModel->getAttribute('domain_name'),
        ];

        $response = $this->json('post', route('fpbx.post.user.login'), $data);
        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected domain name is invalid."]
            ]
        ]);

        $domain_name = $this->testHelperService->getUniqueDomain();
        $data = [
            'username' => $data['username'],
            'password' => $this->faker->password(),
            "domain_name" => $domain_name,
        ];

        $response = $this->json('post', route('fpbx.post.user.login'), $data);
        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected domain name is invalid."]
            ]
        ]);
    }
}
