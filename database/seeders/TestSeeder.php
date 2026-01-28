<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Position;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Medication;
use App\Models\Appointment;
use App\Models\MedicalCheckup;
use App\Models\Vaccination;
use App\Models\PrescribedMed;
use App\Models\MedicalCertificate;
use App\Models\User;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // 0. ROLES & PERMISSIONS
        // ==========================================
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // Create an Admin user
        $admin = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@uitm.edu.my',
            'password' => bcrypt('uitmadminaccess'),
        ]);
        $admin->assignRole('system_admin');

        // Create Test Users for other roles
        $roles = ['head_office', 'doctor', 'staff', 'patient'];
        foreach ($roles as $role) {
            $user = User::factory()->create([
                'name' => ucfirst(str_replace('_', ' ', $role)) . ' User',
                'email' => $role . '@uitm.edu.my',
                'password' => bcrypt('asdfasdf'),
            ]);
            $user->assignRole($role);
        }

        // ==========================================
        // 1. LOOKUP DATA (Departments, Positions, Meds)
        // ==========================================

        // Generate bulk Lookup Data
        $departments = Department::factory()->count(5)->create();
        $positions = Position::factory()->count(5)->create();
        $medications = Medication::factory()->count(20)->create();

        // Specific entries for testing
        $cardio = Department::create([
            "dept_name" => "Cardiology",
            "dept_HP" => "0388889999",
            "dept_email" => "cardio@hospital.com",
        ]);

        $surgery = Department::create([
            "dept_name" => "General Surgery",
            "dept_HP" => "0377776666",
            "dept_email" => "surgery@hospital.com",
        ]);

        $consultant = Position::create([
            "position_name" => "Senior Consultant",
            "position_desc" => "Expert Specialist",
        ]);

        $resident = Position::create([
            "position_name" => "Resident",
            "position_desc" => "Junior Doctor in training",
        ]);

        $panadol = Medication::create([
            "meds_name" => "Paracetamol 500mg",
            "meds_type" => "Tablet",
        ]);

        // ==========================================
        // 2. PEOPLE (Patients & Doctors)
        // ==========================================

        // Generate bulk Patients
        Patient::factory()->count(20)->create();

        // Generate bulk Doctors
        $doctors = Doctor::factory()->count(10)->sequence(fn($sequence) => [
            'dept_ID' => $departments->random()->dept_ID,
            'position_ID' => $positions->random()->position_ID,
        ])->create();

        // Specific entries for testing
        $patientAli = Patient::create([
            "patient_name" => "Ali Bin Abu",
            "patient_gender" => "M",
            "patient_DOB" => "1995-05-15",
            "patient_HP" => "0123456789",
            "patient_email" => "ali@student.edu",
            "patient_type" => "STUDENT",
            "patient_meds_history" => "Allergic to Peanuts",
        ]);

        $drStrange = Doctor::create([
            "doctor_name" => "Dr. Stephen Strange",
            "doctor_gender" => "M",
            "doctor_HP" => "0112223333",
            "doctor_email" => "strange@hospital.com",
            "position_ID" => $consultant->position_ID,
            "dept_ID" => $surgery->dept_ID,
        ]);

        $drHouse = Doctor::create([
            "doctor_name" => "Dr. Gregory House",
            "doctor_gender" => "M",
            "doctor_HP" => "0114445555",
            "doctor_email" => "house@hospital.com",
            "position_ID" => $resident->position_ID,
            "dept_ID" => $cardio->dept_ID,
            "supervisor_ID" => $drStrange->doctor_ID,
        ]);

        // ==========================================
        // 3. TRANSACTIONS (Appointments & Details)
        // ==========================================

        // Generate bulk Appointments with related data
        Appointment::factory()->count(50)->create()->each(function ($appt) use ($medications) {
            $type = rand(1, 3);
            if ($type == 1) {
                // Medical Checkup + Prescription + MC
                MedicalCheckup::factory()->create(['appt_ID' => $appt->appt_ID]);
                PrescribedMed::factory()->count(rand(1, 3))->create([
                    'appt_ID' => $appt->appt_ID,
                    'meds_ID' => $medications->random()->meds_ID,
                ]);
                MedicalCertificate::factory()->create(['appt_ID' => $appt->appt_ID]);
            } elseif ($type == 2) {
                // Vaccination
                Vaccination::factory()->create(['appt_ID' => $appt->appt_ID]);
            }
            // type 3 is just a scheduled appointment with no details yet
        });

        // Specific transactions for testing
        $appt1 = Appointment::create([
            "appt_date" => now()->subDays(2),
            "appt_time" => "09:00 AM",
            "appt_status" => "Completed",
            "appt_payment" => 50.0,
            "appt_note" => "Patient complained of fever",
            "patient_ID" => $patientAli->patient_ID,
            "doctor_ID" => $drHouse->doctor_ID,
        ]);

        MedicalCheckup::create([
            "appt_ID" => $appt1->appt_ID,
            "checkup_symptom" => "High fever, sore throat",
            "checkup_test" => "Temperature check, throat swab",
            "checkup_finding" => "Viral fever",
            "checkup_treatment" => "Rest and hydration",
        ]);

        PrescribedMed::create([
            "appt_ID" => $appt1->appt_ID,
            "meds_ID" => $panadol->meds_ID,
            "amount" => "10 strips",
            "dosage" => "2 tablets every 6 hours",
        ]);

        MedicalCertificate::create([
            "appt_ID" => $appt1->appt_ID,
            "MC_date_start" => now()->subDays(2),
            "MC_date_end" => now()->subDays(1),
        ]);

        echo "\n✅ [TestSeeder] Oracle Database Seeded Successfully with Extended Data!\n";
    }

}
