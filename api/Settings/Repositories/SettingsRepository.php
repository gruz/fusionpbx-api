<?php

namespace Api\Settings\Repositories;

use Api\Settings\Models\Setting;
use Infrastructure\Database\Eloquent\Repository;

class SettingRepository extends Repository
{
    public function getModel()
    {
        return new Setting();
    }

    public function create(array $data)
    {
        $setting = $this->getModel();

        $setting->fill($data);
        $setting->save();

        return $setting;
    }

    public function update(Setting $setting, array $data)
    {
        $setting->fill($data);

        $setting->save();

        return $setting;
    }

}
