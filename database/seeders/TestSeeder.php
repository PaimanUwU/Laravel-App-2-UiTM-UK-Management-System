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

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // 1. LOOKUP DATA (Departments, Positions, Meds)
        // ==========================================

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

        $coughSyrup = Medication::create([
            "meds_name" => "Cough Syrup",
            "meds_type" => "Liquid",
        ]);

        // ==========================================
        // 2. PEOPLE (Patients & Doctors)
        // ==========================================

        $patientAli = Patient::create([
            "patient_name" => "Ali Bin Abu",
            "patient_gender" => "M",
            "patient_DOB" => "1995-05-15",
            "patient_HP" => "0123456789",
            "patient_email" => "ali@student.edu",
            "patient_type" => "STUDENT",
            "patient_meds_history" => "Allergic to Peanuts",
        ]);

        $patientSiti = Patient::create([
            "patient_name" => "Siti Sarah",
            "patient_gender" => "F",
            "patient_DOB" => "1988-12-20",
            "patient_HP" => "0198765432",
            "patient_email" => "siti@staff.edu",
            "patient_type" => "STAFF",
            "patient_meds_history" => "None",
        ]);

        // Doctor 1: The Supervisor
        $drStrange = Doctor::create([
            "doctor_name" => "Dr. Stephen Strange",
            "doctor_gender" => "M",
            "doctor_HP" => "0112223333",
            "doctor_email" => "strange@hospital.com",
            "position_ID" => $consultant->position_ID,
            "dept_ID" => $surgery->dept_ID,
            "supervisor_ID" => null, // No supervisor
        ]);

        // Doctor 2: The Supervised (Resident)
        $drHouse = Doctor::create([
            "doctor_name" => "Dr. Gregory House",
            "doctor_gender" => "M",
            "doctor_HP" => "0114445555",
            "doctor_email" => "house@hospital.com",
            "position_ID" => $resident->position_ID,
            "dept_ID" => $cardio->dept_ID,
            "supervisor_ID" => $drStrange->doctor_ID, // Self-referencing FK
        ]);

        // ==========================================
        // 3. TRANSACTIONS (Appointments & Details)
        // ==========================================

        // Case A: Completed Checkup + Prescription + MC
        $appt1 = Appointment::create([
            "appt_date" => now()->subDays(2),
            "appt_time" => "09:00 AM",
            "appt_status" => "Completed",
            "appt_payment" => 50.0,
            "appt_note" => "Patient complained of fever",
            "patient_ID" => $patientAli->patient_ID,
            "doctor_ID" => $drHouse->doctor_ID,
        ]);

        // 1:1 Relationship - Checkup Details
        MedicalCheckup::create([
            "appt_ID" => $appt1->appt_ID, // Shared PK
            "checkup_symptom" => "High fever, sore throat",
            "checkup_test" => "Temperature check, throat swab",
            "checkup_finding" => "Viral fever",
            "checkup_treatment" => "Rest and hydration",
        ]);

        // 1:Many Relationship - Prescriptions
        PrescribedMed::create([
            "appt_ID" => $appt1->appt_ID,
            "meds_ID" => $panadol->meds_ID,
            "amount" => "10 strips",
            "dosage" => "2 tablets every 6 hours",
        ]);

        // 1:1 Relationship - MC
        MedicalCertificate::create([
            "MC_ID" => 1001, // Optional: manually set ID if desired, or let auto-increment
            "appt_ID" => $appt1->appt_ID,
            "MC_date_start" => now()->subDays(2),
            "MC_date_end" => now()->subDays(1),
        ]);

        // Case B: Vaccination Appointment (No Checkup, No MC)
        $appt2 = Appointment::create([
            "appt_date" => now()->addDays(5),
            "appt_time" => "10:30 AM",
            "appt_status" => "Scheduled",
            "appt_payment" => 0.0,
            "appt_note" => "Flu Shot",
            "patient_ID" => $patientSiti->patient_ID,
            "doctor_ID" => $drStrange->doctor_ID,
        ]);

        // 1:1 Relationship - Vaccination Details
        Vaccination::create([
            "appt_ID" => $appt2->appt_ID, // Shared PK
            "vacc_for" => "Influenza",
            "vacc_desc" => "Annual flu shot required for staff",
        ]);

        echo "\n✅ [TestSeeder] Oracle Database Seeded Successfully!\n";
    }
}
