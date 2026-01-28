<?php

namespace Database\Factories;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFactory extends Factory
{
  protected $model = Medication::class;

  public function definition(): array
  {
    return [
      'meds_name' => $this->faker->unique()->words(2, true),
      'meds_type' => $this->faker->randomElement(['Tablet', 'Capsule', 'Liquid', 'Injection', 'Cream']),
      'stock_quantity' => $this->faker->numberBetween(0, 1000),
      'min_threshold' => $this->faker->numberBetween(10, 50),
    ];
  }
}
