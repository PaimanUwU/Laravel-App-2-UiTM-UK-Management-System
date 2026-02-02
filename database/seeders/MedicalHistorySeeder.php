<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\MedicalCheckup;
use Illuminate\Support\Facades\DB;

class MedicalHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        // Get patients and doctors using DB facade to avoid model issues
        $patients = DB::table('patients')->limit(50)->get();
        $doctors = DB::table('doctors')->limit(20)->get();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->error('No patients or doctors found in database. Please create basic data first.');
            return;
        }

        $createdCount = 0;
        $patientIds = $patients->pluck('patient_id')->toArray();
        $doctorIds = $doctors->pluck('doctor_id')->toArray();

        // Create 200 medical history records
        for ($i = 0; $i < 200; $i++) {
            try {
                // Create a completed appointment first using DB facade
                $appointmentId = DB::table('appointments')->insertGetId([
                    'appt_date' => now()->subDays(rand(1, 365))->format('Y-m-d'),
                    'appt_time' => sprintf('%02d:%02d', rand(8, 17), rand(0, 59)),
                    'appt_status' => 'Completed',
                    'appt_payment' => rand(50, 500) + (rand(0, 99) / 100),
                    'appt_note' => 'Follow-up consultation',
                    'patient_ID' => $patientIds[array_rand($patientIds)],
                    'doctor_ID' => $doctorIds[array_rand($doctorIds)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ], 'appt_ID');

                // Generate random vitals data
                $vitalBp = sprintf('%d/%d', rand(90, 140), rand(60, 90));
                $vitalHeartRate = rand(60, 100);
                $vitalWeight = rand(50, 100) + (rand(0, 9) / 10);
                $vitalHeight = rand(150, 190) + (rand(0, 9) / 10);

                // Then create the medical checkup using DB facade with vitals
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
                    $medications = DB::table('medications')->limit(10)->get();
                    if ($medications->isNotEmpty()) {
                        $med = $medications->random();
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
                $this->command->error("Error creating record {$i}: " . $e->getMessage());
                continue;
            }
        }

        $this->command->info("Successfully created {$createdCount} medical history records!");
    }
}
