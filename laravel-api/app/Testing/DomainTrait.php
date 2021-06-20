<?php

namespace App\Testing;

use App\Models\Domain;

trait DomainTrait
{
    private function prepareNonExistingDomain() {
        $nonExistingDomain = $this->faker->domainName;
        while (true) {
            $domain = Domain::where('domain_name', $nonExistingDomain)->first();
            if (empty($domain)) {
                break;
            } else {
                $nonExistingDomain = $this->faker->domainName;
            }
        }

        return $nonExistingDomain;

    }
}
