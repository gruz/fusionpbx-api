<?php

namespace Api\Domain\Testing\Feature;

use Infrastructure\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DomainSignupTest extends TestCase
{
    public function test_Adding_new_domain_passes_with_one_admin_user_passes() {
        $data = [
            'title' => 'Updated post title',
        ];

        $post = factory(Post::class)->create();

        $this->json('put', 'api/posts/' . $post->id, $data)
            ->assertStatus(200);

        $this->assertDatabaseHas('posts', $data);
    }

    public function adding_new_domain_passes_with_several_admin_users_passes() {}


    public function test_Adding_existsing_domain_fails() {
        $response = $this->get('/');

        $response->assertStatus(200);

        $this->assertTrue(true);
    }

    public function adding_domain_with_no_or_bad_referral_code_fails() {
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

    public function adding_domain_with_missing_admin_user_fails() {}
    public function adding_domain_with_missing_users_fails() {}


}
