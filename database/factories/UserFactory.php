<?php

namespace Database\Factories;

use App\Models\User;
use App\Services\TestHelperService;
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
        $domain_name = app(TestHelperService::class)->getUniqueDomain();
        $return = [
            "is_admin" => false,
            // "reseller_reference_code" => "IS_TEST_CODE",
            "username" => $email,
            "user_email" => $email,
            "password" => $this->faker->password() . '0aA',
            "domain_name" => $domain_name,
        ];

        return $return;

    }
}
