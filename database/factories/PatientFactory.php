<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
  protected $model = Patient::class;

  public function definition(): array
  {
    return [
      'user_id' => User::factory(),
      'patient_name' => $this->faker->name(),
      'patient_gender' => $this->faker->randomElement(['M', 'F']),
      'patient_DOB' => $this->faker->date(),
      'patient_HP' => $this->faker->phoneNumber(),
      'patient_email' => $this->faker->unique()->safeEmail(),
      'patient_type' => $this->faker->randomElement(['STUDENT', 'STAFF', 'DEPENDENT']),
      'patient_meds_history' => $this->faker->optional()->sentence(),
      'student_id' => $this->faker->optional()->numerify('202#########'),
      'ic_number' => $this->faker->numerify('######-##-####'),
      'phone' => $this->faker->phoneNumber(),
      'address' => $this->faker->address(),
      'date_of_birth' => $this->faker->date(),
      'gender' => $this->faker->randomElement(['Male', 'Female']),
    ];
  }
}
