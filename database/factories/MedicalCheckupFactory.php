<?php

namespace Database\Factories;

use App\Models\MedicalCheckup;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalCheckupFactory extends Factory
{
  protected $model = MedicalCheckup::class;

  public function definition(): array
  {
    return [
      'appt_id' => Appointment::factory(),
      'checkup_symptom' => $this->faker->sentence(),
      'checkup_test' => $this->faker->sentence(),
      'checkup_finding' => $this->faker->sentence(),
      'checkup_treatment' => $this->faker->sentence(),
    ];
  }
}
