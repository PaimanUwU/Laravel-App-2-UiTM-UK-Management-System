<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Doctor;
use Carbon\Carbon;

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
            'password' => bcrypt('asdfasdf'),
        ]);
        $admin->assignRole('system_admin');

        // Create Test Users for other roles
        $roles = ['head_office', 'doctor', 'patient'];
        foreach ($roles as $role) {
            $user = User::factory()->create([
                'name' => ucfirst(str_replace('_', ' ', $role)) . ' User',
                'email' => $role . '@uitm.edu.my',
                'password' => bcrypt('asdfasdf'),
            ]);
            $user->assignRole($role);

            if ($role === 'doctor') {
                Doctor::factory()->create([
                    'user_id' => $user->id,
                    'doctor_name' => $user->name,
                    'doctor_email' => $user->email,
                    'status' => 'ACTIVE',
                ]);
            }
        }

        // ==========================================================
        // 1. REFERENCE DATA (DEPARTMENTS & POSITIONS)
        // ==========================================================
        DB::table('Department')->insert([
            ['dept_ID' => 1, 'dept_name' => 'Medical', 'dept_HP' => '0355443833', 'dept_email' => 'pusatkesihatan@uitm.edu.my'],
            ['dept_ID' => 2, 'dept_name' => 'Dental', 'dept_HP' => '0355443630', 'dept_email' => 'pusatkesihatan@uitm.edu.my'],
        ]); [cite: 71, 72]

        DB::table('Position')->insert([
            ['position_ID' => 1, 'position_name' => 'Medical Officer', 'position_desc' => 'Treat complicated medical cases such as chronic diseases, severe infections, and detailed diagnosis.'],
            ['position_ID' => 2, 'position_name' => 'Dental Officer', 'position_desc' => 'Treat complicated dental cases such as surgeries, root canals, extractions, and advanced oral care.'],
            ['position_ID' => 3, 'position_name' => 'Assistant Medical Officer', 'position_desc' => 'Treat minor injuries such as cuts and wounds, and handle basic triage under supervision of Medical Officers.'],
            ['position_ID' => 4, 'position_name' => 'Dental Therapist', 'position_desc' => 'Treat minor dental cases such as routine cleaning (scaling) and polishing under supervision of Dental Officers.'],
        ]); [cite: 73, 74]

        // ==========================================================
        // 2. DOCTORS (Seniors first then Juniors)
        // ==========================================================
        DB::table('Doctor')->insert([
            ['doctor_ID' => 2010123456, 'name' => 'Dr. Shairul Azam', 'gender' => 'M', 'dob' => '1975-05-20', 'phone' => '0123456789', 'email' => 'shairul@uitm.edu.my', 'position_ID' => 1, 'dept_ID' => 1, 'supervisor_ID' => null],
            ['doctor_ID' => 2012987654, 'name' => 'Dr. Siti Hafifah', 'gender' => 'F', 'dob' => '1980-08-15', 'phone' => '0198765432', 'email' => 'siti@uitm.edu.my', 'position_ID' => 1, 'dept_ID' => 1, 'supervisor_ID' => null],
            ['doctor_ID' => 2014223344, 'name' => 'Dr. Azarifa Abdullah', 'gender' => 'F', 'dob' => '1982-02-14', 'phone' => '0162233445', 'email' => 'azarifa@uitm.edu.my', 'position_ID' => 2, 'dept_ID' => 2, 'supervisor_ID' => null],
            // Add other senior doctors here...
        ]); [cite: 75, 76]

        DB::table('Doctor')->insert([
            ['doctor_ID' => 2020555001, 'name' => 'PPP Ahmad Razak', 'gender' => 'M', 'dob' => '1996-06-15', 'phone' => '0115550001', 'email' => 'ahmadppp@uitm.edu.my', 'position_ID' => 3, 'dept_ID' => 1, 'supervisor_ID' => 2010123456],
            ['doctor_ID' => 2022111222, 'name' => 'JT Lisa Karim', 'gender' => 'F', 'dob' => '1998-07-07', 'phone' => '0111112222', 'email' => 'lisa@uitm.edu.my', 'position_ID' => 4, 'dept_ID' => 2, 'supervisor_ID' => 2014223344],
        ]); [cite: 77]

        // ==========================================================
        // 3. PATIENTS
        // ==========================================================
        DB::table('Patient')->insert([
            ['patient_ID' => 2023456789, 'name' => 'Ali Bin Abu', 'gender' => 'M', 'dob' => '2001-05-12', 'phone' => '0123456789', 'email' => 'ali@student.uitm.edu.my', 'history' => 'Asthma (Mild)', 'type' => 'STUDENT'],
            ['patient_ID' => 2005123456, 'name' => 'Pn. Rohana Binti Yusof', 'gender' => 'F', 'dob' => '1975-03-15', 'phone' => '0134455667', 'email' => 'rohana@uitm.edu.my', 'history' => 'Hypertension, takes medicine daily', 'type' => 'STAFF'],
        ]); [cite: 79]

        // ==========================================================
        // 4. MEDICATIONS & STOCK
        // ==========================================================
        DB::table('Medication')->insert([
            ['meds_ID' => 6001, 'meds_name' => 'Paracetamol 500mg', 'meds_type' => 'Pill'],
            ['meds_ID' => 6002, 'meds_name' => 'Amoxicillin 250mg', 'meds_type' => 'Capsule'],
        ]); [cite: 83, 84]

        DB::table('Stock_Movements')->insert([
            ['stock_ID' => 1, 'meds_ID' => 6001, 'quantity' => 500, 'type' => 'IN', 'reason' => 'Initial stock - Paracetamol', 'created_at' => '2025-01-01'],
        ]); [cite: 94]

        // ==========================================================
        // 5. APPOINTMENTS
        // ==========================================================
        DB::table('Appointment')->insert([
            ['appt_ID' => 10001, 'appt_date' => '2024-01-10', 'appt_time' => '09:30', 'status' => 'DISCHARGED', 'fee' => 0.00, 'remarks' => 'Student presented with high fever', 'patient_ID' => 2023456789, 'doctor_ID' => 2010123456],
        ]); [cite: 97]

        // ==========================================================
        // 6. SUBTYPE DETAILS
        // ==========================================================
        DB::table('Medical_Checkup')->insert([
            ['appt_ID' => 10001, 'symptoms' => 'Headache, temp 39C, sore throat', 'findings' => 'Throat swab negative', 'diagnosis' => 'Viral Fever', 'treatment' => 'Prescribed antipyretics and rest'],
        ]); [cite: 106]

        DB::table('Vaccination')->insert([
            ['appt_ID' => 10003, 'vax_type' => 'Typhoid', 'next_dose_date' => '2027-02-01', 'vax_remarks' => 'Typhim Vi vaccine given.'],
        ]); [cite: 108]

        echo "\n✅ [TestSeeder] Oracle Database Seeded Successfully with Extended Data!\n";
    }
}
