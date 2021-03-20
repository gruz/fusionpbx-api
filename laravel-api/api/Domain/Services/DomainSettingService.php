<?php

namespace Api\Domain\Services;

use Api\Domain\Repositories\DomainSettingRepository;
use Illuminate\Support\Facades\Auth;

class DomainSettingService
{
    
    private $domainSettingRepository;

    public function __construct(
        DomainSettingRepository $domainSettingRepository
    ) {
        $this->domainSettingRepository = $domainSettingRepository;
    }

    public function getAll($options = [])
    {
        return $this->domainSettingRepository
                    ->getWhereArray(['domain_uuid' => Auth::user()->domain_uuid]);
    }

    public function getById($domainSettingId, array $options = [])
    {
        $data = $this->domainSettingRepository
                     ->getWhere('domain_setting_uuid', $domainSettingId)
                     ->first();

        return $data;
    }


    public function getByAttributes(array $attributes)
    {
        $data = null;

        if (!empty($attributes) && !is_null($attributes)) {
            $data = $this->domainSettingRepository->getWhereArray($attributes);
        }

        return $data;
    }

    public function getByAttributeValues($attribute, array $values)
    {
        $data = null;

        if (
            !empty($attribute) && !is_null($attribute) &&
            !empty($values) && !is_null($values) && is_array($values)
        ) 
        {
            $data = $this->domainSettingRepository->getWhereIn($attribute, $values)->toArray();
        }

        return $data;
    }

}
