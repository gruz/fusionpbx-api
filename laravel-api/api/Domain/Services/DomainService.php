<?php

namespace Api\Domain\Services;

use Infrastructure\Database\Eloquent\AbstractService;

class DomainService extends AbstractService
{
    public function getDomainsArray()
    {
        $domainsCollection = $this->repository->getWhere('domain_enabled', true);
        $domains = $domainsCollection->pluck('domain_name', 'domain_uuid')->toArray();
        return $domains;
    }
}
