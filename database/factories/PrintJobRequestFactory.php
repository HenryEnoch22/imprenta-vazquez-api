<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\TypeReceipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PrintJobRequest>
 */
class PrintJobRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            // Relaciones
            'customer_id'      => Customer::factory(),
            'type_receipt_id'  => TypeReceipt::factory(),

            // Campos propios
            'name'             => $this->faker->words(3, true),
            'file_path'        => $this->faker->filePath(),
            'description'      => $this->faker->sentence(10),
            'folio'            => $this->faker->optional()->uuid(),

            // Paper settings
            'paper_size'       => $this->faker->randomElement(['Carta', 'Oficio', 'Tabloide']),
            'copies_number'    => $this->faker->numberBetween(1, 100),

            // JSON fields
            'copies_colors'    => [
                'front' => $this->faker->randomElement(['color', 'b/n']),
                'back'  => $this->faker->randomElement(['color', 'b/n']),
            ],

            'tint_colors'      => [
                'cyan'    => $this->faker->numberBetween(0, 100),
                'magenta' => $this->faker->numberBetween(0, 100),
                'yellow'  => $this->faker->numberBetween(0, 100),
                'black'   => $this->faker->numberBetween(0, 100),
            ],

            'paper_type'       => $this->faker->numberBetween(1, 5),
            'quantity'         => $this->faker->numberBetween(1, 5000),

            // Status: 1 = pending, 2 = in process, 3 = finished, 4 = rejected (ejemplo)
            'status'           => $this->faker->numberBetween(1, 4),

            'reason_rejection' => $this->faker->optional()->sentence(8),

            'created_at'       => now(),
            'updated_at'       => now(),
        ];
    }
}
