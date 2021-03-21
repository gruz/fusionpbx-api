<?php

namespace Database\Factories\Api\Settings\Models;

use Api\Settings\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;


class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Setting::class;

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
                "domain_setting_category" => "domain",
                "domain_setting_subcategory" => "language",
                "domain_setting_name" => "code",
                "domain_setting_value" => "uk-ua",
                "domain_setting_order" => 0,
                "domain_setting_enabled" => true,
                "domain_setting_description" => $this->faker->text(),
            ],
            [
                "domain_setting_category" => "voicemail",
                "domain_setting_subcategory" => "message_order",
                "domain_setting_name" => "text",
                "domain_setting_value" => "desc",
                "domain_setting_order" => 0,
                "domain_setting_enabled" => true,
                "domain_setting_description" => 'Play oldest voicemail first',
            ],
        ];

        if (false === $used || count($items) === $used ) {
            $used = 0;
        }

        $return = $items[$used];
        $return['domain_setting_order'] = $order;

        $used++;
        $order++;

        return $return;
    }
}
