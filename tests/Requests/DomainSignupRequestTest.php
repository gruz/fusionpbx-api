<?php

namespace Tests\Requests;

use Illuminate\Support\Arr;
use Gruz\FPBX\Models\Domain;
use Tests\TestCase;
use Gruz\FPBX\Services\TestRequestFactoryService;

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

    public function testFailWhenInvalidDomain()
    {
        $data = $this->testRequestFactoryService->makeDomainSignupRequest();
        $data['domain_name'] = $this->faker->sentence();

        $response = $this->json('post', route('fpbx.domain.signup', $data));

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "Should be a valid hostname."]
            ]
        ]);
    }

    public function testFailWhenNoUsers()
    {
        $data = $this->testRequestFactoryService->makeDomainSignupRequest();
        unset($data['users']);

        $response = $this->json('post', route('fpbx.domain.signup', $data));

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The users field is required."]
            ]
        ]);
    }

    public function testFailWhenNoOrBadReferenceCode()
    {
        $data = $this->testRequestFactoryService->makeDomainSignupRequest();

        config(['fpbx.resellerCode.required' => true]);

        $response = $this->json('post', route('fpbx.domain.signup', $data));
        $response->assertStatus(422);
        $response->assertJsonFragment(["detail" => "The reseller reference code field is required."]);

        $data['reseller_reference_code'] = uniqid();
        $response = $this->json('post', route('fpbx.domain.signup', $data));
        $response->assertStatus(422);
        $response->assertJsonFragment(["detail" => "The selected reseller reference code is invalid."]);
    }

    public function testFailIfSameEmails()
    {
        $data = $this->testRequestFactoryService->makeDomainSignupRequest();
        $data = $this->makeDuplicatedUserEmails($data);

        $response = $this->json('post', route('fpbx.domain.signup', $data));
        $response->assertStatus(422);
        $response->assertJsonFragment(["detail" => "The users.0.user_email field has a duplicate value."]);
    }

    public function testFailNoUserEmail()
    {
        $data = $this->testRequestFactoryService->makeDomainSignupRequest();
        $data = $this->removeEmails($data);

        $response = $this->json('post', route('fpbx.domain.signup', $data));
        $response->assertStatus(422);
        $response->assertJsonFragment(["detail" => "The users.0.user_email field is required."]);
    }

    public function testFail_If_no_admin_among_user()
    {
        /**
         * @var TestRequestFactoryService
         */
        $testRequestFactoryService = app(TestRequestFactoryService::class);
        $data = $testRequestFactoryService->makeDomainSignupRequest();
        foreach ($data['users'] as &$user) {
            $user['is_admin'] = false;
        }

        // dd(collect($data['users'])->pluck('is_admin'));

        $response = $this->json('post', route('fpbx.domain.signup', $data));
        $response->assertStatus(422);
        $response->assertJsonFragment(["detail" => "At least one `is_admin` to be true is needed"]);
    }

    private function makeDuplicatedUserEmails($data)
    {
        Arr::set($data, 'users.0.user_email', 'a@a.com');
        Arr::set($data, 'users.1.user_email', 'a@a.com');

        return $data;
    }

    private function removeEmails($data)
    {
        Arr::forget($data, 'users.0.user_email');
        Arr::set($data, 'users.1.user_email', '');

        return $data;
    }
}
