<?php

namespace Tests\Requests;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\PostponedAction;

class DomainActivateRequestTest extends TestCase
{
    public function testFailBad_hash_not_uuid_fails()
    {
        $data = [
            'email' => $this->faker->email,
            'hash' => 'somee',
        ];

        $route = route('fpbx.get.domain.activate', $data);
        $response = $this->json('get', $route);

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The hash must be a valid UUID."]
            ]
        ]);
    }

    public function testFailBad_hash_and_email()
    {
        $data = [
            'email' => $this->faker->email,
            'hash' => Str::uuid()->toString(),
        ];

        $route = route('fpbx.get.domain.activate', $data);
        $response = $this->json('get', $route);

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "The selected hash is invalid."],
                ["detail" => "Activation link for your email not found"]
            ]
        ]);
    }

    public function testFailsExpiredLink()
    {
        list($request, $response) = $this->simulateDomainSignup();

        $model = PostponedAction::first();

        $second = $model->replicate();
        $second->hash = Str::uuid()->toString();
        $second->created_at = Carbon::now()->subCentury();
        $second->save();

        $hash = $model->hash;
        $emails = Arr::get($request, 'users');
        $emails = collect($emails)->pluck('user_email');
        $address = $emails[0];


        $data = [
            'email' => $address,
            'hash' => $second->hash,
        ];

        $route = route('fpbx.get.domain.activate', $data);
        $response = $this->json('get', $route);

        $response->assertStatus(422);
        $response->assertJson([
            "errors" => [
                ["detail" => "Domain activation link expired"],
                ["detail" => "Activation link for your email not found"]
            ]
        ]);
    }
}
