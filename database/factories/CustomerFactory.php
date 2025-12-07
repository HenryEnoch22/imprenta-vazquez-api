<?php

namespace Database\Factories;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'address_id' => CustomerAddress::factory(),
            'business_name' => $this->faker->company(),
            'representative_name' => $this->faker->name(),
            'rfc' => $this->faker->bothify('????######???'),
            'phone_number' => $this->faker->numerify('##############'),
        ];
    }
}
