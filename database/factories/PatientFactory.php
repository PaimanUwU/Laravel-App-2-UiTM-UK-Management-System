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
        $email = $this->faker->unique()->safeEmail();
        $name = $this->faker->name();

        return [
            "user_id" => User::factory()->create([
                "email" => $email,
                "name" => $name,
            ])->id,
            "patient_name" => $name,
            "patient_gender" => $this->faker->randomElement(["M", "F"]),
            "patient_dob" => $this->faker->date(),
            "patient_hp" => $this->faker->phoneNumber(),
            "patient_email" => $email,
            "patient_type" => $this->faker->randomElement(["STUDENT", "STAFF"]),
            "patient_meds_history" => $this->faker->optional()->sentence(),
            "student_id" => $this->faker->optional()->numerify("202#########"),
            "ic_number" => $this->faker->numerify("######-##-####"),
            "phone" => $this->faker->phoneNumber(),
            "address" => $this->faker->address(),
            "date_of_birth" => $this->faker->date(),
            "gender" => $this->faker->randomElement(["Male", "Female"]),
        ];
    }
}
