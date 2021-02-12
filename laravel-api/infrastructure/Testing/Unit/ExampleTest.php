<?php

namespace Tests\Unit;

use Infrastructure\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_a_basic_request()
    {
        $response = $this->get('/');
        $response->dumpHeaders();

        $response->dumpSession();

        $response->dump();

        $response->assertStatus(200);

        // $response = $this->postJson('/api/user', ['name' => 'Sally']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'title' => 'FusionPBX API',
            ]);

        // $this->assertTrue($response['title']);

        // $response
        //     ->assertStatus(201)
        //     ->assertJsonPath('team.owner.name', 'Darian');
    }

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function atest_interacting_with_headers()
    {
        $response = $this->withHeaders([
            'X-Header' => 'Value',
        ])->post('/user', ['name' => 'Sally']);

        $response->assertStatus(201);
    }

    public function atest_interacting_with_cookies()
    {
        $response = $this->withCookie('color', 'blue')->get('/');

        $response = $this->withCookies([
            'color' => 'blue',
            'name' => 'Taylor',
        ])->get('/');
    }

    public function atest_interacting_with_the_session()
    {
        $response = $this->withSession(['banned' => false])->get('/');
    }
}
