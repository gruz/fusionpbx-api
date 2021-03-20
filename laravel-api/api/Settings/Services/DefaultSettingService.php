<?php

namespace Api\Settings\Services;

use Api\Settings\Repositories\DefaultSettingRepository;
use Illuminate\Support\Facades\Auth;

class DefaultSettingService
{
    
    private $defaultSettingRepository;

    public function __construct(
        DefaultSettingRepository $defaultSettingRepository
    ) {
        $this->defaultSettingRepository = $defaultSettingRepository;
    }

    public function getAll($options = [])
    {
        return $this->defaultSettingRepository
                    ->getWhereArray(['domain_uuid' => Auth::user()->domain_uuid]);
    }

    public function getById($defaultSettingId, array $options = [])
    {
        $data = $this->defaultSettingRepository
                     ->getWhere('default_setting_uuid', $defaultSettingId)
                     ->first();

        return $data;
    }


    public function getByAttributes(array $attributes)
    {
        $data = null;

        if (!empty($attributes) && !is_null($attributes)) {
            $data = $this->defaultSettingRepository->getWhereArray($attributes);
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
            $data = $this->defaultSettingRepository->getWhereIn($attribute, $values)->toArray();
        }

        return $data;
    }

}
