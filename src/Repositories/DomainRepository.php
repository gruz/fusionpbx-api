<?php

namespace Gruz\FPBX\Repositories;

use Gruz\FPBX\Models\Domain;
use Gruz\FPBX\Repositories\AbstractRepository;

class DomainRepository extends AbstractRepository
{
    public function getDomainByName($domain_name, $status = null) : Domain
    {
        static $storage = [];
        $skey = __FUNCTION__ . serialize(func_get_args());
        if (array_key_exists($skey, $storage)) {
            return $storage[$skey];
        }

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

        $storage[$skey] = $domainModel->first();
        return $storage[$skey];
    }
}
