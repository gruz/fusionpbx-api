<?php

namespace Database\Factories;

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
        do {
            $domain_name = $this->faker->domainName;
        } while (
            Domain::where('domain_name', $domain_name)->count() > 0
        );

        return [
            'domain_name' => $domain_name,
            'domain_enabled' => true,
            'domain_description' => 'Created via Factory during tests',
        ];
    }
}
