<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
  protected $model = Appointment::class;

  public function definition(): array
  {
    return [
      'appt_date' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
      'appt_time' => $this->faker->time('H:i A'),
      'appt_status' => $this->faker->randomElement(['Scheduled', 'Completed', 'Cancelled']),
      'appt_payment' => $this->faker->randomFloat(2, 0, 100),
      'appt_note' => $this->faker->sentence(),
      'patient_ID' => Patient::factory(),
      'doctor_ID' => Doctor::factory(),
    ];
  }
}
