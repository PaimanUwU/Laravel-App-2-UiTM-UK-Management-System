<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
  protected $model = Doctor::class;

  public function definition(): array
  {
    return [
      'doctor_name' => $this->faker->name('male'),
      'doctor_gender' => $this->faker->randomElement(['M', 'F']),
      'doctor_dob' => $this->faker->date(),
      'doctor_hp' => $this->faker->phoneNumber(),
      'doctor_email' => $this->faker->unique()->safeEmail(),
      'position_id' => Position::factory(),
      'dept_id' => Department::factory(),
      'supervisor_id' => null,
      'status' => $this->faker->randomElement(['Active', 'Inactive']),
    ];
  }
}
