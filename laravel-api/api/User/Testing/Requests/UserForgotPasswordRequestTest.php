<?php

namespace Api\User\Testing\Requests;

use Faker\Factory;
use Illuminate\Support\Arr;
use Api\User\Models\User;
use Api\Domain\Models\Domain;
use Infrastructure\Testing\TestCase;
use Infrastructure\Services\TestRequestFactoryService;
use Api\Extension\Models\Extension;

class UserForgotPasswordRequestTest extends TestCase
{
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

        $data = $this->testRequestFactoryService->makeUserForgotPasswordRequest();

        $data['domain_name'] = $nonExistingDomain;

        $response = $this->json('post', route('fpbx.post.forgot-password'), $data);

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected domain name is invalid."]
            ]
        ]);
    }

    public function testFailWhenUserNotExistsInADomain() 
    {
        $nonExistingEmail = $this->faker->email;
        while (true) {
            $email = User::where('user_email', $nonExistingEmail)->first();
            if (empty($email)) {
                break;
            } else {
                $nonExistingEmail = $this->faker->email;
            }
        }

        list($response, $email) = $this->simulateDomainSignupAndActivate();
        $data = $this->testRequestFactoryService->makeUserForgotPasswordRequest();
        $data['user_email'] = $nonExistingEmail;
        $data['domain_name'] = $response->json('domain_name');

        $response = $this->json('post', route('fpbx.post.forgot-password', $data));

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected user email is invalid."]
            ]
        ]);
    }

    public function testFailWhenUserAndDomainNotExists() 
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

        $nonExistingEmail = $this->faker->email;
        while (true) {
            $email = User::where('user_email', $nonExistingEmail)->first();
            if (empty($email)) {
                break;
            } else {
                $nonExistingEmail = $this->faker->email;
            }
        }

        $data = $this->testRequestFactoryService->makeUserForgotPasswordRequest();

        $data['domain_name'] = $nonExistingDomain;
        $data['user_email'] = $nonExistingEmail;

        $response = $this->json('post', route('fpbx.post.forgot-password'), $data);
        
        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected domain name is invalid."],
                ["detail" => "The selected user email is invalid."]
            ]
        ]);
    }

    public function testFailWhenUserEmailIsWrongFormat()
    {
        
    } 
}
