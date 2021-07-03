<?php

namespace App\Services;

use Faker\Generator;
use App\Models\Domain;

class TestHelperService {
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function getUniqueDomain() {
        do {
            $domain_name = $this->faker->domainName;
        } while (
            Domain::where('domain_name', $domain_name)->count() > 0
        );

        return $domain_name;
    }
}