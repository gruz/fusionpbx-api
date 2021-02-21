<?php

namespace Database\Factories\Api\User\Models;

use Illuminate\Support\Arr;
use Api\User\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;


class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $contactTypes = [
            'customer',
            'contractor',
            'friend',
            'lead',
            'member',
            'family',
            'subscriber',
            'supplier',
            'provider',
            'user',
            'volunteer',
        ];
        // d($contactTypes[array_rand($contactTypes)]);
        $contactType = Arr::random($contactTypes);
        $contactType = Arr::random([ $contactType, config('fpbx.default.contact_type')]);

        $return = [
            "contact_type" => $contactType,
            "contact_organization" => $this->faker->company,
            "contact_name_prefix" => $this->faker->title,
            "contact_name_given" => $this->faker->firstName,
            "contact_name_middle" => "A.",
            "contact_name_family" => $this->faker->lastName,
            "contact_name_suffix" => $this->faker->suffix,
            "contact_nickname" => $this->faker->userName,
            "contact_title" => $this->faker->title,
            "contact_role" => $this->faker->jobTitle,
            "contact_category" => "Contacts added via API",
            "contact_url" => $this->faker->url,
            "contact_time_zone" => $this->faker->timezone,
            "contact_note" => $this->faker->text(),
        ];

        return $return;

    }
}
