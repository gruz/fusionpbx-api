<?php

namespace Infrastructure\Services;

use GuzzleHttp\Client;

class CGRTService
{
    /**
     * @var Client
     */
    public $client;

    public function __construct($base_url, $username, $password)
    {
        $client = new Client(['base_uri' => $base_url]);

        $response = $client->post('users/get_token',  [
            'json' => [
                'username' => $username,
                'password' => $password,
            ]
        ]);

        $token = json_decode($response->getBody()->getContents())->token;

        $this->client = new Client([
            'base_uri' => $base_url,
            'headers' => ['Authorization' => 'Token ' . $token]
        ]);

        return $this;
    }

    public function getTenants()
    {
        $r = $this->client->post('users/get_tenants_list');
        $results = json_decode($r->getBody()->getContents())->results;

        return $results;
    }

    public function getReferenceCodes()
    {
        $tenants = $this->getTenants();
        $reseller_codes = [];
        foreach ($tenants as $key => $tenant) {
            $clients = json_decode($this->client->post('users/get_clients', ['json' => ['tenant' => $tenant]])->getBody()->getContents())->results;
            $clients = collect($clients);
            $account_codes = $clients->pluck('account_code')->toArray();
            $reseller_codes = array_merge($reseller_codes, $account_codes);
        }

        return $reseller_codes;
    }
}
