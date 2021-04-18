<?php

namespace Database\Factories\Api\User\Models;

use Api\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $email = $this->faker->email;
        $return = [
            "is_admin" => false,
            // "reseller_reference_code" => "IS_TEST_CODE",
            "username" => $email,
            "user_email" => $email,
            "password" => $this->faker->password,
            "domain_name" => $this->faker->domainName,
        ];

        return $return;

    }
}
