<?php

namespace Gruz\FPBX\Repositories;

use Gruz\FPBX\Models\Domain;
use Gruz\FPBX\Repositories\AbstractRepository;

class DomainRepository extends AbstractRepository
{
    public function getDomainByName($domain_name, $status = null) : Domain
    {
        /**
         * @var \Gruz\FPBX\Models\Domain
         */
        $domainModel = $this->getModel()->whereRaw('LOWER(domain_name) = ?', [strtolower($domain_name)]);
        if (null !== $status) {
            if (config('domain_enabled_field_type') === 'text') {
                $status = $status ? 'true' : 'false';
            }

            $domainModel->where('domain_enabled', $status);
        }

        return $domainModel->first();
    }
}
