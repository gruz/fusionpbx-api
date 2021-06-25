<?php

namespace Database\Factories\App\Models;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;


class DomainFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Domain::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'domain_name' => $this->faker->domainName,
            'domain_enabled' => true,
            'domain_description' => 'Created via Factory during tests',
        ];
    }
}
