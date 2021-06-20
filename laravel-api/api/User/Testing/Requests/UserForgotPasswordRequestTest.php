<?php

namespace App\Requests;

use App\Models\User;
use App\Models\Domain;
use App\Testing\TestCase;
use App\Testing\UserTrait;
use App\Testing\DomainTrait;

class UserForgotPasswordRequestTest extends TestCase
{
    use DomainTrait;
    use UserTrait;
    public function testFailsIfDomainNotExists()
    {
        list($response, $email) = $this->simulateDomainSignupAndActivate();
        $data['user_email'] = $email;
        $data['domain_name'] = $this->prepareNonExistingDomain();

        $response = $this->json('post', route('fpbx.user.forgot-password'), $data);

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected domain name is invalid."]
            ]
        ]);
    }

    public function testFailWhenDomainIsNotEnabled()
    {
        list($response, $email) = $this->simulateDomainSignupAndActivate();

        $domainModel = Domain::where('domain_uuid', $response->json('domain_uuid'))->first();
        $domainModel->domain_enabled = false;
        $domainModel->save();

        $data['user_email'] = $email;
        $data['domain_name'] = $domainModel->getAttribute('domain_name');

        $response = $this->json('post', route('fpbx.user.forgot-password', $data));

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected domain name is invalid."]
            ]
        ]);
    }

    public function testFailWhenUserIsNotEnabled()
    {
        list($response, $email) = $this->simulateDomainSignupAndActivate();

        $userModel = User::where('domain_uuid', $response->json('domain_uuid'))
            ->where('user_email', $email)
            ->first();
        $userModel->user_enabled = false;
        $userModel->save();

        $data['user_email'] = $email;
        $data['domain_name'] = $response->json('domain_name');

        $response = $this->json('post', route('fpbx.user.forgot-password', $data));

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "User disabled"]
            ]
        ]);
    }

    public function testFailWhenUserNotExistsInADomain()
    {
        list($response, $email) = $this->simulateDomainSignupAndActivate();

        $data['user_email'] = $this->prepareNonExistingEmailInDomain($response->json('domain_uuid'));
        $data['domain_name'] = $response->json('domain_name');

        $response = $this->json('post', route('fpbx.user.forgot-password', $data));

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected user email is invalid."]
            ]
        ]);
    }

    public function testFailWhenUserAndDomainNotExists()
    {
        $nonExistingDomain = $this->prepareNonExistingDomain();

        $nonExistingEmail = $this->faker->email;
        while (true) {
            $email = User::where('user_email', $nonExistingEmail)->first();
            if (empty($email)) {
                break;
            } else {
                $nonExistingEmail = $this->faker->email;
            }
        }

        $data['domain_name'] = $nonExistingDomain;
        $data['user_email'] = $nonExistingEmail;

        $response = $this->json('post', route('fpbx.user.forgot-password'), $data);

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected domain name is invalid."],
                ["detail" => "The selected user email is invalid."]
            ]
        ]);
    }
}
