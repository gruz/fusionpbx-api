<?php

namespace Gruz\FPBX\Services\Fpbx;

use Gruz\FPBX\Models\Domain;
use Illuminate\Contracts\Container\BindingResolutionException;

class DomainService extends AbstractService
{
    public function getDomainsArray($use_uuid_as_key = false)
    {
        $domainsCollection = $this->getByAttributes(['domain_enabled' => true]);
        $domains = $domainsCollection->pluck('domain_name', 'domain_uuid')->toArray();

        if ($use_uuid_as_key) {
            $domains = array_combine(array_values($domains), $domains);
        }
        return $domains;
    }

    public function getSystemDomain()
    {
        $systemDomainName = config('fpbx.default.domain.mothership_domain');
        $domain = $this->getByAttributes(['domain_enabled' => true, 'domain_name' => $systemDomainName], ['limit' => 1])->first();
        if (!$domain) {
            $domain = $this->getByAttributes(['domain_enabled' => true], ['limit' => 1])->first();
        }

        return $domain;
    }

    public function getByAttributes(array $attributes, $options = [])
    {
        $domain_enabled = \Arr::get($attributes, 'domain_enabled', null);

        if (null !== $domain_enabled) {
            if (config('domain_enabled_field_type') === 'text') {
                $domain_enabled = $domain_enabled ? 'true' : 'false';
            }

            $attributes['domain_enabled'] = $domain_enabled;
        }

        return parent::getByAttributes($attributes, $options);
    }

    /**
     * 
     * @param mixed $domain_name 
     * @param bool|string $status True or false
     * @return Domain 
     * @throws BindingResolutionException 
     */
    public function getDomainByName($domain_name, $status = null): Domain
    {
        return $this->getRepository()->getDomainByName($domain_name, $status);
    }

    public function getDomainFromRequest()
    {
        static $storage = [];
        $skey = __FUNCTION__ . serialize(func_get_args());
        if (array_key_exists($skey, $storage)) {
            return $storage[$skey];
        }

        $domain_uuid = request()->get('domain_uuid');

        if ($domain_uuid) {
            $domainModel = $this->getByAttributes(['domain_uuid' => $domain_uuid]);
        } else {
            $domainModel = false;
        }

        $storage[$skey] = $domainModel;

        return $domainModel;
    }
}
