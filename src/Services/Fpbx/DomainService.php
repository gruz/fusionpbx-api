<?php

namespace Gruz\FPBX\Services\Fpbx;

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

    public function getDomainByName($domain_name, $status = null)
    {
        return $this->getRepository()->getDomainByName($domain_name, $status);
    }
}
