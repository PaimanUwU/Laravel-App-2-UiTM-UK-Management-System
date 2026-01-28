<?php

namespace Database\Factories;

use App\Models\MedicalCertificate;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalCertificateFactory extends Factory
{
  protected $model = MedicalCertificate::class;

  public function definition(): array
  {
    $startDate = $this->faker->dateTimeBetween('-1 month', 'now');
    $endDate = clone $startDate;
    $endDate->modify('+' . $this->faker->numberBetween(1, 5) . ' days');

    return [
      'MC_date_start' => $startDate->format('Y-m-d'),
      'MC_date_end' => $endDate->format('Y-m-d'),
      'appt_ID' => Appointment::factory(),
    ];
  }
}
