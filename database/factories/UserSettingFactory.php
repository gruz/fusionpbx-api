<?php

namespace Database\Factories;

use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Factories\Factory;


class UserSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $used = false;
        static $order = 0;

        $items = [
            [
                "user_setting_category" => "payment",
                "user_setting_subcategory" => "reseller_code",
                "user_setting_name" => "text",
                "user_setting_value" => "SOME CODE",
                "user_setting_order" => 0,
                "user_setting_enabled" => true,
                "user_setting_description" => 'Reseller code used for payment',
            ],
            [
                "user_setting_category" => "users",
                "user_setting_subcategory" => "password_length",
                "user_setting_name" => "numeric",
                "user_setting_value" => "10",
                "user_setting_order" => 0,
                "user_setting_enabled" => true,
                "user_setting_description" => 'Password length',
            ],
        ];

        if (false === $used || count($items) === $used ) {
            $used = 0;
        }

        $return = $items[$used];
        $return['user_setting_order'] = $order;

        $used++;
        $order++;

        return $return;
    }
}
