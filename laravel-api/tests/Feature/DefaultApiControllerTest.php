<?php

namespace Tests\Feature;

use Tests\TestCase;

class DefaultApiControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testIndex_Success()
    {
        $response = $this->get('/');
        // $response->dumpHeaders();

        // $response->dumpSession();

        // $response->dump();

        $response
            ->assertStatus(200)
            ->assertJson([
                'title' => 'FusionPBX API',
        ]);

    }
}
