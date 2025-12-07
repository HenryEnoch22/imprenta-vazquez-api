<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerAddress>
 */
class CustomerAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'postal_code' => $this->faker->numerify('#####'),
            'address' => $this->faker->streetAddress(),
            'locality_name' => $this->faker->city(),
            'federal_entity' => $this->faker->state(),
            'neighborhood' => $this->faker->streetName(),
            'municipality' => $this->faker->citySuffix(),
            'between_streets' => $this->faker->streetSuffix() . ' and ' . $this->faker->streetSuffix(),
            'interior_number' => $this->faker->optional()->buildingNumber(),
            'exterior_number' => $this->faker->buildingNumber(),
        ];
    }
}
