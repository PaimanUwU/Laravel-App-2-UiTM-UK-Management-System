<?php

namespace Database\Factories;

use App\Models\PrescribedMed;
use App\Models\Appointment;
use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescribedMedFactory extends Factory
{
  protected $model = PrescribedMed::class;

  public function definition(): array
  {
    return [
      'appt_id' => Appointment::factory(),
      'meds_id' => Medication::factory(),
      'amount' => $this->faker->numberBetween(1, 10),
      'dosage' => $this->faker->sentence(),
    ];
  }
}
