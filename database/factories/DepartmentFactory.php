<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
  protected $model = Department::class;

  public function definition(): array
  {
    return [
      'dept_name' => $this->faker->unique()->company() . ' Department',
      'dept_HP' => $this->faker->numerify('03########'),
      'dept_email' => $this->faker->unique()->safeEmail(),
    ];
  }
}
