<?php

namespace Web\User\Testing\Feature;

use Infrastructure\Testing\TestCase;

class UserControllerTest extends TestCase
{
    public function testGetNewPasswordFormSuccess()
    {
        $url = url(route('password.forgot'));
        $response = $this->json('get', $url);
        // $response->assertViewHas('Reset Password');
        $response->assertSee('<input id="domain_name" type="text"', false);
        $response->assertSee('<input id="user_email" type="email"', false);
    }

}
