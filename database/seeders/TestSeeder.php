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
            'password' => bcrypt('asdfasdf'),
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

            // If the role is doctor, create a linked doctor profile
            if ($role === 'doctor') {
                Doctor::factory()->create([
                    'user_id' => $user->id,
                    'doctor_name' => $user->name,
                    'doctor_email' => $user->email,
                    'status' => 'ACTIVE',
                ]);
            }
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
            "dept_hp" => "0388889999",
            "dept_email" => "cardio@hospital.com",
        ]);

        $surgery = Department::create([
            "dept_name" => "General Surgery",
            "dept_hp" => "0377776666",
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

        // Generate bulk Patients with user accounts
        Patient::factory()->count(20)->create()->each(function ($patient) {
            if ($patient->user_id) {
                $user = User::find($patient->user_id);
                if ($user && !$user->hasRole('patient')) {
                    $user->assignRole('patient');
                }
            }
        });

        // Generate bulk Doctors with user accounts
        $doctors = Doctor::factory()->count(10)->sequence(fn($sequence) => [
            'dept_id' => $departments->random()->dept_id,
            'position_id' => $positions->random()->position_id,
        ])->create()->each(function ($doctor) {
            if ($doctor->user_id) {
                $user = User::find($doctor->user_id);
                if ($user && !$user->hasRole('doctor')) {
                    $user->assignRole('doctor');
                }
            }
        });

        // Specific entries for testing
        $aliUser = User::factory()->create([
            'name' => 'Ali Bin Abu',
            'email' => 'ali@student.edu',
            'password' => bcrypt('asdfasdf'),
        ]);
        $aliUser->assignRole('patient');

        $patientAli = Patient::create([
            "user_id" => $aliUser->id,
            "patient_name" => "Ali Bin Abu",
            "patient_gender" => "M",
            "patient_dob" => "1995-05-15",
            "patient_hp" => "0123456789",
            "patient_email" => "ali@student.edu",
            "patient_type" => "STUDENT",
            "patient_meds_history" => "Allergic to Peanuts",
        ]);

        $strangeUser = User::factory()->create([
            'name' => 'Dr. Stephen Strange',
            'email' => 'strange@hospital.com',
            'password' => bcrypt('asdfasdf'),
        ]);
        $strangeUser->assignRole('doctor');

        $drStrange = Doctor::create([
            "user_id" => $strangeUser->id,
            "doctor_name" => "Dr. Stephen Strange",
            "doctor_gender" => "M",
            "doctor_hp" => "0112223333",
            "doctor_email" => "strange@hospital.com",
            "position_id" => $consultant->position_id,
            "dept_id" => $surgery->dept_id,
        ]);

        $houseUser = User::factory()->create([
            'name' => 'Dr. Gregory House',
            'email' => 'house@hospital.com',
            'password' => bcrypt('asdfasdf'),
        ]);
        $houseUser->assignRole('doctor');

        $drHouse = Doctor::create([
            "user_id" => $houseUser->id,
            "doctor_name" => "Dr. Gregory House",
            "doctor_gender" => "M",
            "doctor_hp" => "0114445555",
            "doctor_email" => "house@hospital.com",
            "position_id" => $resident->position_id,
            "dept_id" => $cardio->dept_id,
            "supervisor_id" => $drStrange->doctor_id,
        ]);

        // ==========================================
        // 3. TRANSACTIONS (Appointments & Details)
        // ==========================================

        // Generate bulk Appointments with related data (reduced from 50 to 20)
        Appointment::factory()->count(20)->create()->each(function ($appt) use ($medications) {
            $type = rand(1, 3);
            if ($type == 1) {
                // Medical Checkup + Prescription + MC
                MedicalCheckup::factory()->create(['appt_id' => $appt->appt_id]);
                PrescribedMed::factory()->count(rand(1, 3))->create([
                    'appt_id' => $appt->appt_id,
                    'meds_id' => $medications->random()->meds_id,
                ]);
                MedicalCertificate::factory()->create(['appt_id' => $appt->appt_id]);
            } elseif ($type == 2) {
                // Vaccination
                Vaccination::factory()->create(['appt_id' => $appt->appt_id]);
            }
            // type 3 is just a scheduled appointment with no details yet
        });

        // ==========================================
        // 4. EXTENDED MEDICAL HISTORY (200 Records)
        // ==========================================

        // Sample medical data for realistic medical history
        $symptoms = [
            'Fever and headache for 3 days',
            'Persistent cough with chest pain',
            'Abdominal pain and nausea',
            'Shortness of breath and fatigue',
            'Joint pain and swelling',
            'Skin rash and itching',
            'Dizziness and vertigo',
            'Back pain radiating to legs',
            'Sore throat and difficulty swallowing',
            'Frequent urination and thirst',
        ];

        $tests = [
            'Complete blood count (CBC)',
            'X-ray chest PA view',
            'Ultrasound abdomen',
            'ECG and cardiac enzymes',
            'MRI brain with contrast',
            'CT scan abdomen pelvis',
            'Blood sugar fasting and postprandial',
            'Liver function tests',
            'Kidney function tests',
            'Thyroid profile test',
        ];

        $findings = [
            'Mild upper respiratory tract infection',
            'Gastroenteritis with dehydration',
            'Hypertension stage 1',
            'Type 2 Diabetes Mellitus',
            'Musculoskeletal strain',
            'Allergic dermatitis',
            'Viral fever',
            'Acute bronchitis',
            'Peptic ulcer disease',
            'Anemia due to iron deficiency',
        ];

        $treatments = [
            'Prescribed antibiotics and paracetamol',
            'IV fluids and antiemetics',
            'Antihypertensive medication started',
            'Oral hypoglycemic agents prescribed',
            'Physical therapy and pain medication',
            'Topical steroids and antihistamines',
            'Supportive care and rest advised',
            'Bronchodilators and expectorants',
            'PPIs and dietary modifications',
            'Iron supplements and dietary advice',
        ];

        // Get patients and doctors for extended medical history
        $allPatients = DB::table('patients')->pluck('patient_id')->toArray();
        $allDoctors = DB::table('doctors')->pluck('doctor_id')->toArray();

        if (!empty($allPatients) && !empty($allDoctors)) {
            $createdCount = 0;

            // Create 100 additional medical history records (reduced from 200)
            for ($i = 0; $i < 100; $i++) {
                try {
                    // Create a completed appointment first using DB facade
                    $appointmentId = DB::table('appointments')->insertGetId([
                        'appt_date' => now()->subDays(rand(31, 365))->format('Y-m-d'),
                        'appt_time' => sprintf('%02d:%02d', rand(8, 17), rand(0, 59)),
                        'appt_status' => 'Completed',
                        'appt_payment' => rand(50, 500) + (rand(0, 99) / 100),
                        'appt_note' => 'Follow-up consultation',
                        'patient_ID' => $allPatients[array_rand($allPatients)],
                        'doctor_ID' => $allDoctors[array_rand($allDoctors)],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], 'appt_ID');

                    // Generate random vitals data
                    $vitalBp = sprintf('%d/%d', rand(90, 140), rand(60, 90));
                    $vitalHeartRate = rand(60, 100);
                    $vitalWeight = rand(50, 100) + (rand(0, 9) / 10);
                    $vitalHeight = rand(150, 190) + (rand(0, 9) / 10);

                    // Create the medical checkup using DB facade with vitals
                    DB::table('medical_checkups')->insert([
                        'appt_ID' => $appointmentId,
                        'checkup_symptom' => $symptoms[array_rand($symptoms)],
                        'checkup_test' => $tests[array_rand($tests)],
                        'checkup_finding' => $findings[array_rand($findings)],
                        'checkup_treatment' => $treatments[array_rand($treatments)],
                        'vital_bp' => $vitalBp,
                        'vital_heart_rate' => $vitalHeartRate,
                        'vital_weight' => $vitalWeight,
                        'vital_height' => $vitalHeight,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Randomly create medical certificates (30% chance)
                    if (rand(1, 100) <= 30) {
                        $mcDays = rand(1, 7);
                        $mcStartDate = now()->subDays(rand(1, 30))->format('Y-m-d');
                        $mcEndDate = \Carbon\Carbon::parse($mcStartDate)->addDays($mcDays - 1)->format('Y-m-d');
                        
                        DB::table('medical_certificates')->insert([
                            'appt_ID' => $appointmentId,
                            'MC_date_start' => $mcStartDate,
                            'MC_date_end' => $mcEndDate,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Randomly create prescribed medications (40% chance)
                    if (rand(1, 100) <= 40) {
                        // Get some medications from the database
                        $availableMeds = DB::table('medications')->limit(10)->get();
                        if ($availableMeds->isNotEmpty()) {
                            $med = $availableMeds->random();
                            $dosages = ['1x daily', '2x daily', '3x daily', '1x before meal', '1x after meal'];
                            $amounts = [7, 14, 21, 28, 30];
                            
                            DB::table('prescribed_meds')->insert([
                                'appt_ID' => $appointmentId,
                                'meds_ID' => $med->meds_id,
                                'amount' => $amounts[array_rand($amounts)],
                                'dosage' => $dosages[array_rand($dosages)],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    $createdCount++;
                } catch (\Exception $e) {
                    echo "Error creating medical record {$i}: " . $e->getMessage() . "\n";
                    continue;
                }
            }

            echo "✅ Created {$createdCount} additional medical history records!\n";
        }

        // ==========================================
        // 5. PAST 30-DAY APPOINTMENTS FOR TREND CHART
        // ==========================================

        // Create 150 past appointments spanning the past 30 days (increased from 40)
        $allPatients = DB::table('patients')->pluck('patient_id')->toArray();
        $allDoctors = DB::table('doctors')->pluck('doctor_id')->toArray();

        if (!empty($allPatients) && !empty($allDoctors)) {
            $createdCount = 0;
            
            // Create 150 appointments distributed across the past 30 days (increased from 40)
            for ($i = 0; $i < 150; $i++) {
                try {
                    // Distribute appointments across the past 30 days
                    $daysAgo = rand(1, 30);
                    $appointmentDate = now()->subDays($daysAgo);
                    
                    // Random time between 8 AM and 5 PM
                    $hour = rand(8, 16);
                    $minute = rand(0, 59);
                    $appointmentTime = sprintf('%02d:%02d', $hour, $minute);
                    
                    // Random status with higher probability for completed
                    $statuses = ['Completed', 'Completed', 'Completed', 'scheduled', 'Confirmed'];
                    $status = $statuses[array_rand($statuses)];
                    
                    $appointmentId = DB::table('appointments')->insertGetId([
                        'appt_date' => $appointmentDate->format('Y-m-d'),
                        'appt_time' => $appointmentTime,
                        'appt_status' => $status,
                        'appt_payment' => rand(30, 200) + (rand(0, 99) / 100),
                        'appt_note' => $status === 'Completed' ? 'Completed consultation' : 'Upcoming appointment',
                        'patient_ID' => $allPatients[array_rand($allPatients)],
                        'doctor_ID' => $allDoctors[array_rand($allDoctors)],
                        'created_at' => $appointmentDate->copy()->subHours(rand(1, 24)),
                        'updated_at' => $status === 'Completed' ? $appointmentDate->copy()->addHours(rand(1, 4)) : $appointmentDate,
                    ], 'appt_ID');

                    // For completed appointments, add medical details
                    if ($status === 'Completed') {
                        // Generate realistic vitals
                        $vitalBp = sprintf('%d/%d', rand(90, 140), rand(60, 90));
                        $vitalHeartRate = rand(60, 100);
                        $vitalWeight = rand(50, 100) + (rand(0, 9) / 10);
                        $vitalHeight = rand(150, 190) + (rand(0, 9) / 10);

                        DB::table('medical_checkups')->insert([
                            'appt_ID' => $appointmentId,
                            'checkup_symptom' => $symptoms[array_rand($symptoms)],
                            'checkup_test' => $tests[array_rand($tests)],
                            'checkup_finding' => $findings[array_rand($findings)],
                            'checkup_treatment' => $treatments[array_rand($treatments)],
                            'vital_bp' => $vitalBp,
                            'vital_heart_rate' => $vitalHeartRate,
                            'vital_weight' => $vitalWeight,
                            'vital_height' => $vitalHeight,
                            'created_at' => $appointmentDate->copy()->addMinutes(30),
                            'updated_at' => $appointmentDate->copy()->addMinutes(30),
                        ]);

                        // 30% chance of medical certificate
                        if (rand(1, 100) <= 30) {
                            $mcDays = rand(1, 3);
                            $mcStartDate = $appointmentDate->format('Y-m-d');
                            $mcEndDate = \Carbon\Carbon::parse($mcStartDate)->addDays($mcDays - 1)->format('Y-m-d');
                            
                            DB::table('medical_certificates')->insert([
                                'appt_ID' => $appointmentId,
                                'MC_date_start' => $mcStartDate,
                                'MC_date_end' => $mcEndDate,
                                'created_at' => $appointmentDate->copy()->addMinutes(45),
                                'updated_at' => $appointmentDate->copy()->addMinutes(45),
                            ]);
                        }

                        // 40% chance of prescribed medication
                        if (rand(1, 100) <= 40) {
                            $availableMeds = DB::table('medications')->limit(10)->get();
                            if ($availableMeds->isNotEmpty()) {
                                $med = $availableMeds->random();
                                $dosages = ['1x daily', '2x daily', '3x daily', '1x before meal', '1x after meal'];
                                $amounts = [7, 14, 21, 28];
                                
                                DB::table('prescribed_meds')->insert([
                                    'appt_ID' => $appointmentId,
                                    'meds_ID' => $med->meds_id,
                                    'amount' => $amounts[array_rand($amounts)],
                                    'dosage' => $dosages[array_rand($dosages)],
                                    'created_at' => $appointmentDate->copy()->addMinutes(60),
                                    'updated_at' => $appointmentDate->copy()->addMinutes(60),
                                ]);
                            }
                        }
                    }

                    $createdCount++;
                } catch (\Exception $e) {
                    echo "Error creating past appointment {$i}: " . $e->getMessage() . "\n";
                    continue;
                }
            }

            echo "✅ Created {$createdCount} past appointments for trend chart!\n";
        }

        // Specific transactions for testing
        $appt1 = Appointment::create([
            "appt_date" => now()->subDays(2),
            "appt_time" => "09:00 AM",
            "appt_status" => "Completed",
            "appt_payment" => 50.0,
            "appt_note" => "Patient complained of fever",
            "patient_id" => $patientAli->patient_id,
            "doctor_id" => $drHouse->doctor_id,
        ]);

        MedicalCheckup::create([
            "appt_id" => $appt1->appt_id,
            "checkup_symptom" => "High fever, sore throat",
            "checkup_test" => "Temperature check, throat swab",
            "checkup_finding" => "Viral fever",
            "checkup_treatment" => "Rest and hydration",
            "vital_bp" => "120/80",
            "vital_heart_rate" => 80,
            "vital_weight" => 70.5,
            "vital_height" => 175.0,
        ]);

        PrescribedMed::create([
            "appt_id" => $appt1->appt_id,
            "meds_id" => $panadol->meds_id,
            "amount" => "10 strips",
            "dosage" => "2 tablets every 6 hours",
        ]);

        MedicalCertificate::create([
            "appt_id" => $appt1->appt_id,
            "mc_date_start" => now()->subDays(2),
            "mc_date_end" => now()->subDays(1),
        ]);

        echo "\n✅ [TestSeeder] Oracle Database Seeded Successfully with Extended Data!\n";
    }

}
