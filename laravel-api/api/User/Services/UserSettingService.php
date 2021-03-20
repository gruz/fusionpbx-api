<?php

namespace Api\User\Services;

use Api\User\Repositories\UserSettingRepository;
use Illuminate\Support\Facades\Auth;

class UserSettingService
{
    
    private $userSettingRepository;

    public function __construct(
        UserSettingRepository $userSettingRepository
    ) {
        $this->userSettingRepository = $userSettingRepository;
    }

    public function getAll($options = [])
    {
        return $this->userSettingRepository
                    ->getWhereArray(['domain_uuid' => Auth::user()->domain_uuid]);
    }

    public function getById($userSettingId, array $options = [])
    {
        $data = $this->userSettingRepository
                     ->getWhere('user_setting_uuid', $userSettingId)
                     ->first();

        return $data;
    }


    public function getByAttributes(array $attributes)
    {
        $data = null;

        if (!empty($attributes) && !is_null($attributes)) {
            $data = $this->userSettingRepository->getWhereArray($attributes);
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
            $data = $this->userSettingRepository->getWhereIn($attribute, $values)->toArray();
        }

        return $data;
    }

}
