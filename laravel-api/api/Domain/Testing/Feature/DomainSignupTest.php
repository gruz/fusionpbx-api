<?php

namespace Api\Domain\Testing\Feature;

use Infrastructure\Testing\TestCase;
use Api\Domain\Requests\DomainSignupRequest;
use Infrastructure\Services\TestRequestFactoryService;

class DomainSignupTest extends TestCase
{
    /**
     * @var TestRequestFactoryService
     */
    private $testRequestFactoryService;

    public function setUp(): void {

        parent::setUp();

        $this->testRequestFactoryService = app(TestRequestFactoryService::class);
    }

    public function test_Adding_new_subDomain() {}
    public function test_Adding_new_domain_passes_with_one_admin_user_passes()
    {
        $this->withoutExceptionHandling();
        // $this->expectException(\Exception::class);
        $data = $this->testRequestFactoryService->makeDomainRequest();
        // \Illuminate\Support\Arr::set($data, 'users.0.user_email', 'a@a.com');
        // \Illuminate\Support\Arr::set($data, 'users.1.user_email', 'a@a.com');
        // \Illuminate\Support\Arr::set($data, 'users.0.is_admin', false);
        // \Illuminate\Support\Arr::set($data, 'users.1.is_admin', false);
        // \Illuminate\Support\Arr::set($data, 'users.2.is_admin', false);

        // $data['domain_name'] = '192.168.0.160';
        $domain['domain_name'] = $data['domain_name'];

        $response = $this->json('post', '/domain/signup', $data);
        \Illuminate\Support\Facades\Storage::put('request.json', json_encode($data, JSON_PRETTY_PRINT));

        $response->dump();
        // dd($response);
        $response->assertStatus(201);

        $this->assertDatabaseHas('v_domains', $domain);
    }

    public function test_Adding_new_domain_passes_with_several_admin_users_passes()
    {
    }


    public function test_Adding_existsing_domain_fails()
    {
        // $response = $this->get('/');

        // $response->assertStatus(200);

        // $this->assertTrue(true);
    }

    public function test_Adding_domain_with_no_or_bad_referral_code_fails()
    {
        return;
        $data = [
            'domain_name' => generate(),
            //
        ];

        $response = $this->post('/domain/signup', $data);

        $response->assertStatus(400);

        $data = [
            'domain_name' => generate(),
            'rereral_code' => 'bad',
            //
        ];

        $response = $this->post('/domain/signup', $data);

        $response->assertStatus(400);
    }

    public function adding_domain_with_missing_admin_user_fails()
    {
    }
    public function adding_domain_with_missing_users_fails()
    {
    }
    public function badPassword_adding_domain_with_missing_users_fails()
    {
    }
    public function duplicated_username_fails()
    {
    }

}
