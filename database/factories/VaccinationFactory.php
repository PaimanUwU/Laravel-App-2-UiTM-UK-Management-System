<?php

namespace Database\Factories;

use App\Models\Vaccination;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class VaccinationFactory extends Factory
{
  protected $model = Vaccination::class;

  public function definition(): array
  {
    return [
      'appt_id' => Appointment::factory(),
      'vacc_for' => $this->faker->randomElement(['Influenza', 'COVID-19', 'Hepatitis B', 'HPV']),
      'vacc_exp_date' => $this->faker->dateTimeBetween('+6 months', '+2 years')->format('Y-m-d'),
      'vacc_desc' => $this->faker->sentence(),
    ];
  }
}
