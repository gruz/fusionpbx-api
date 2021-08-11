<?php

namespace Gruz\FPBX\Database\Factories;

use Gruz\FPBX\Models\Domain;
use Gruz\FPBX\Services\TestHelperService;
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
        $domain_name = app(TestHelperService::class)->getUniqueDomain();

        return [
            'domain_name' => $domain_name,
            'domain_enabled' => true,
            'domain_description' => 'Created via Factory during tests',
        ];
    }
}
