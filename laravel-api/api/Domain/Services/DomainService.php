<?php

namespace Api\Domain\Services;

use Infrastructure\Database\Eloquent\AbstractService;

class DomainService extends AbstractService
{
    public function getDomainsArray($use_uuid_as_key = false)
    {
        $domainsCollection = $this->repository->getWhere('domain_enabled', true);
        $domains = $domainsCollection->pluck('domain_name', 'domain_uuid')->toArray();
        if ($use_uuid_as_key) {
            $domains = array_combine(array_values($domains),$domains);
        }
        return $domains;
    }

    public function getSystemDomain() {
        return $this->repository->getWhere('domain_enabled', true, ['limit' => 1])->first();
    }
}
