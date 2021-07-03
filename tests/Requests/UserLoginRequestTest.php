<?php

namespace Tests\Requests;

use Tests\TestCase;
use App\Models\Domain;
use App\Models\Extension;
use Tests\Traits\UserTrait;
use App\Services\Fpbx\ExtensionService;

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

        $data = [
            'username' => $data['username'],
            'password' => $this->faker->password(),
            "domain_name" => $this->faker->domainName,
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
