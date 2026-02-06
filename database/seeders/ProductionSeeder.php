<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Medication;
use App\Models\Appointment;
use App\Models\MedicalCheckup;
use App\Models\Vaccination;
use App\Models\PrescribedMed;
use App\Models\MedicalCertificate;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RolesAndPermissionsSeeder::class]);

        // Departments
        Department::updateOrCreate(['dept_id' => 1], ['dept_name' => 'Medical', 'dept_HP' => '0355443833', 'dept_email' => 'pusatkesihatan@uitm.edu.my']);
        Department::updateOrCreate(['dept_id' => 2], ['dept_name' => 'Dental', 'dept_HP' => '0355443630', 'dept_email' => 'pusatkesihatan@uitm.edu.my']);

        // Positions
        Position::updateOrCreate(['position_id' => 1], ['position_name' => 'Medical Officer', 'position_desc' => 'Treat complicated medical cases such as chronic diseases, severe infections, and detailed diagnosis.']);
        Position::updateOrCreate(['position_id' => 2], ['position_name' => 'Dental Officer', 'position_desc' => 'Treat complicated dental cases such as surgeries, root canals, extractions, and advanced oral care.']);
        Position::updateOrCreate(['position_id' => 3], ['position_name' => 'Assistant Medical Officer', 'position_desc' => 'Treat minor injuries such as cuts and wounds, and handle basic triage under supervision of Medical Officers.']);
        Position::updateOrCreate(['position_id' => 4], ['position_name' => 'Dental Therapist', 'position_desc' => 'Treat minor dental cases such as routine cleaning (scaling) and polishing under supervision of Dental Officers.']);

        $password = Hash::make('asdfasdf');

        // Users - Admin
        $admin = User::updateOrCreate(['id' => 1], ['name' => 'System Admin', 'email' => 'admin@uitm.edu.my', 'password' => $password, 'status' => 'active']);
        $admin->assignRole('system_admin');

        // Users - Doctors
        $doctorUsers = [
            ['id' => 2, 'name' => 'Dr. Shairul Azam', 'email' => 'shairul@uitm.edu.my'],
            ['id' => 3, 'name' => 'Dr. Siti Hafifah', 'email' => 'siti@uitm.edu.my'],
            ['id' => 4, 'name' => 'Dr. Nurfazlinda', 'email' => 'nurfaz@uitm.edu.my'],
            ['id' => 5, 'name' => 'Dr. Amal Farah', 'email' => 'amal@uitm.edu.my'],
            ['id' => 6, 'name' => 'Dr. Razak Osman', 'email' => 'razak@uitm.edu.my'],
            ['id' => 7, 'name' => 'Dr. Azarifa Abdullah', 'email' => 'azarifa@uitm.edu.my'],
            ['id' => 8, 'name' => 'Dr. Hazmyr Wahab', 'email' => 'hazmyr@uitm.edu.my'],
            ['id' => 9, 'name' => 'Dr. Lee Wei Han', 'email' => 'leewei@uitm.edu.my'],
            ['id' => 10, 'name' => 'Dr. Sarah Devi', 'email' => 'sarahd@uitm.edu.my'],
            ['id' => 11, 'name' => 'Dr. Johan Ariff', 'email' => 'johan@uitm.edu.my'],
            ['id' => 12, 'name' => 'Dr. Mei Ling', 'email' => 'mei@uitm.edu.my'],
        ];
        foreach ($doctorUsers as $u) {
            $user = User::updateOrCreate(['id' => $u['id']], ['name' => $u['name'], 'email' => $u['email'], 'password' => $password, 'status' => 'active']);
            $user->assignRole('doctor');
        }

        // Users - Patients
        $patientUsers = [
            ['id' => 101, 'name' => 'Ali Bin Abu', 'email' => 'ali@student.uitm.edu.my'],
            ['id' => 102, 'name' => 'Siti Aminah Binti Akob', 'email' => 'siti@student.uitm.edu.my'],
            ['id' => 103, 'name' => 'Demir Naufal Bin Hasbullah', 'email' => 'demir@student.uitm.edu.my'],
            ['id' => 104, 'name' => 'Pn. Rohana Binti Yusof', 'email' => 'rohana@uitm.edu.my'],
            ['id' => 105, 'name' => 'En. Kamal Ariffin', 'email' => 'kamal@uitm.edu.my'],
            ['id' => 106, 'name' => 'Haziq Bin Rosli', 'email' => 'haziq@student.uitm.edu.my'],
            ['id' => 107, 'name' => 'Stephen Raj A/L Muthu', 'email' => 'stephen@uitm.edu.my'],
            ['id' => 108, 'name' => 'Jessica Wong', 'email' => 'jessica@student.uitm.edu.my'],
            ['id' => 109, 'name' => 'Sarah Binti Nasir', 'email' => 'sarah@student.uitm.edu.my'],
            ['id' => 110, 'name' => 'Raj Kumar', 'email' => 'raj@student.uitm.edu.my'],
            ['id' => 111, 'name' => 'Pn. Salmah Bakar', 'email' => 'salmah@uitm.edu.my'],
            ['id' => 112, 'name' => 'En. Mat Nor', 'email' => 'matnor@uitm.edu.my'],
            ['id' => 113, 'name' => 'Mei Ling', 'email' => 'meiling@student.uitm.edu.my'],
        ];
        foreach ($patientUsers as $u) {
            $user = User::updateOrCreate(['id' => $u['id']], ['name' => $u['name'], 'email' => $u['email'], 'password' => $password, 'status' => 'active']);
            $user->assignRole('patient');
        }

        // Doctors - Supervisors
        $doctors = [
            ['doctor_id' => 2010123456, 'user_id' => 2, 'doctor_name' => 'Dr. Shairul Azam', 'doctor_gender' => 'M', 'doctor_dob' => '1975-05-20', 'doctor_hp' => '0123456789', 'doctor_email' => 'shairul@uitm.edu.my', 'position_id' => 1, 'dept_id' => 1, 'supervisor_id' => null],
            ['doctor_id' => 2012987654, 'user_id' => 3, 'doctor_name' => 'Dr. Siti Hafifah', 'doctor_gender' => 'F', 'doctor_dob' => '1980-08-15', 'doctor_hp' => '0198765432', 'doctor_email' => 'siti@uitm.edu.my', 'position_id' => 1, 'dept_id' => 1, 'supervisor_id' => null],
            ['doctor_id' => 2013567890, 'user_id' => 4, 'doctor_name' => 'Dr. Nurfazlinda', 'doctor_gender' => 'F', 'doctor_dob' => '1982-03-10', 'doctor_hp' => '0135678901', 'doctor_email' => 'nurfaz@uitm.edu.my', 'position_id' => 1, 'dept_id' => 1, 'supervisor_id' => null],
            ['doctor_id' => 2014112233, 'user_id' => 5, 'doctor_name' => 'Dr. Amal Farah', 'doctor_gender' => 'F', 'doctor_dob' => '1984-11-25', 'doctor_hp' => '0141122334', 'doctor_email' => 'amal@uitm.edu.my', 'position_id' => 1, 'dept_id' => 1, 'supervisor_id' => null],
            ['doctor_id' => 2015998877, 'user_id' => 6, 'doctor_name' => 'Dr. Razak Osman', 'doctor_gender' => 'M', 'doctor_dob' => '1985-01-30', 'doctor_hp' => '0179988776', 'doctor_email' => 'razak@uitm.edu.my', 'position_id' => 1, 'dept_id' => 1, 'supervisor_id' => null],
            ['doctor_id' => 2014223344, 'user_id' => 7, 'doctor_name' => 'Dr. Azarifa Abdullah', 'doctor_gender' => 'F', 'doctor_dob' => '1982-02-14', 'doctor_hp' => '0162233445', 'doctor_email' => 'azarifa@uitm.edu.my', 'position_id' => 2, 'dept_id' => 2, 'supervisor_id' => null],
            ['doctor_id' => 2016445566, 'user_id' => 8, 'doctor_name' => 'Dr. Hazmyr Wahab', 'doctor_gender' => 'M', 'doctor_dob' => '1986-07-20', 'doctor_hp' => '0124455667', 'doctor_email' => 'hazmyr@uitm.edu.my', 'position_id' => 2, 'dept_id' => 2, 'supervisor_id' => null],
            ['doctor_id' => 2017778899, 'user_id' => 9, 'doctor_name' => 'Dr. Lee Wei Han', 'doctor_gender' => 'M', 'doctor_dob' => '1987-09-12', 'doctor_hp' => '0197788990', 'doctor_email' => 'leewei@uitm.edu.my', 'position_id' => 2, 'dept_id' => 2, 'supervisor_id' => null],
            ['doctor_id' => 2018001122, 'user_id' => 10, 'doctor_name' => 'Dr. Sarah Devi', 'doctor_gender' => 'F', 'doctor_dob' => '1988-05-05', 'doctor_hp' => '0130011223', 'doctor_email' => 'sarahd@uitm.edu.my', 'position_id' => 2, 'dept_id' => 2, 'supervisor_id' => null],
            ['doctor_id' => 2018334455, 'user_id' => 11, 'doctor_name' => 'Dr. Johan Ariff', 'doctor_gender' => 'M', 'doctor_dob' => '1988-12-01', 'doctor_hp' => '0143344556', 'doctor_email' => 'johan@uitm.edu.my', 'position_id' => 2, 'dept_id' => 2, 'supervisor_id' => null],
            ['doctor_id' => 2018667788, 'user_id' => 12, 'doctor_name' => 'Dr. Mei Ling', 'doctor_gender' => 'F', 'doctor_dob' => '1989-04-18', 'doctor_hp' => '0176677889', 'doctor_email' => 'mei@uitm.edu.my', 'position_id' => 2, 'dept_id' => 2, 'supervisor_id' => null],
        ];
        foreach ($doctors as $d) {
            Doctor::updateOrCreate(['doctor_id' => $d['doctor_id']], array_merge($d, ['status' => 'ACTIVE']));
        }

        // Doctors - Supervisees
        $supervisees = [
            ['doctor_id' => 2020555001, 'user_id' => null, 'doctor_name' => 'PPP Ahmad Razak', 'doctor_gender' => 'M', 'doctor_dob' => '1996-06-15', 'doctor_hp' => '0115550001', 'doctor_email' => 'ahmadppp@uitm.edu.my', 'position_id' => 3, 'dept_id' => 1, 'supervisor_id' => 2010123456],
            ['doctor_id' => 2021666002, 'user_id' => null, 'doctor_name' => 'PPP Nurul Izzah', 'doctor_gender' => 'F', 'doctor_dob' => '1997-03-22', 'doctor_hp' => '0116660002', 'doctor_email' => 'nurulppp@uitm.edu.my', 'position_id' => 3, 'dept_id' => 1, 'supervisor_id' => 2012987654],
            ['doctor_id' => 2022777003, 'user_id' => null, 'doctor_name' => 'PPP Kumar Velu', 'doctor_gender' => 'M', 'doctor_dob' => '1998-11-05', 'doctor_hp' => '0117770003', 'doctor_email' => 'kumarppp@uitm.edu.my', 'position_id' => 3, 'dept_id' => 1, 'supervisor_id' => 2013567890],
            ['doctor_id' => 2023888004, 'user_id' => null, 'doctor_name' => 'PPP Dayang Suhana', 'doctor_gender' => 'F', 'doctor_dob' => '1999-01-30', 'doctor_hp' => '0118880004', 'doctor_email' => 'dayangppp@uitm.edu.my', 'position_id' => 3, 'dept_id' => 1, 'supervisor_id' => 2014112233],
            ['doctor_id' => 2024999005, 'user_id' => null, 'doctor_name' => 'PPP Chong Wei', 'doctor_gender' => 'M', 'doctor_dob' => '2000-09-09', 'doctor_hp' => '0119990005', 'doctor_email' => 'chongppp@uitm.edu.my', 'position_id' => 3, 'dept_id' => 1, 'supervisor_id' => 2015998877],
            ['doctor_id' => 2022111222, 'user_id' => null, 'doctor_name' => 'JT Lisa Karim', 'doctor_gender' => 'F', 'doctor_dob' => '1998-07-07', 'doctor_hp' => '0111112222', 'doctor_email' => 'lisa@uitm.edu.my', 'position_id' => 4, 'dept_id' => 2, 'supervisor_id' => 2014223344],
            ['doctor_id' => 2023333444, 'user_id' => null, 'doctor_name' => 'JT Aminah Yusof', 'doctor_gender' => 'F', 'doctor_dob' => '1999-10-15', 'doctor_hp' => '0113334444', 'doctor_email' => 'aminah@uitm.edu.my', 'position_id' => 4, 'dept_id' => 2, 'supervisor_id' => 2016445566],
            ['doctor_id' => 2024555666, 'user_id' => null, 'doctor_name' => 'JT Kevin Tan', 'doctor_gender' => 'M', 'doctor_dob' => '2001-05-20', 'doctor_hp' => '0115556666', 'doctor_email' => 'kevin@uitm.edu.my', 'position_id' => 4, 'dept_id' => 2, 'supervisor_id' => 2017778899],
        ];
        foreach ($supervisees as $d) {
            Doctor::updateOrCreate(['doctor_id' => $d['doctor_id']], array_merge($d, ['status' => 'ACTIVE']));
        }

        // Patients
        $patients = [
            ['patient_id' => 2023456789, 'user_id' => 101, 'patient_name' => 'Ali Bin Abu', 'patient_gender' => 'M', 'patient_dob' => '2001-05-12', 'patient_hp' => '0123456789', 'patient_email' => 'ali@student.uitm.edu.my', 'patient_type' => 'STUDENT', 'patient_meds_history' => 'Asthma (Mild)', 'ic_number' => '010512-10-1234', 'student_id' => '2023456789', 'gender' => 'M'],
            ['patient_id' => 2022876543, 'user_id' => 102, 'patient_name' => 'Siti Aminah Binti Akob', 'patient_gender' => 'F', 'patient_dob' => '2002-08-20', 'patient_hp' => '0198765432', 'patient_email' => 'siti@student.uitm.edu.my', 'patient_type' => 'STUDENT', 'patient_meds_history' => 'No known allergies', 'ic_number' => '020820-10-5678', 'student_id' => '2022876543', 'gender' => 'F'],
            ['patient_id' => 2024112233, 'user_id' => 103, 'patient_name' => 'Demir Naufal Bin Hasbullah', 'patient_gender' => 'M', 'patient_dob' => '2000-11-05', 'patient_hp' => '0171122334', 'patient_email' => 'demir@student.uitm.edu.my', 'patient_type' => 'STUDENT', 'patient_meds_history' => 'Previous ACL tear (2019)', 'ic_number' => '001105-10-9988', 'student_id' => '2024112233', 'gender' => 'M'],
            ['patient_id' => 2005123456, 'user_id' => 104, 'patient_name' => 'Pn. Rohana Binti Yusof', 'patient_gender' => 'F', 'patient_dob' => '1975-03-15', 'patient_hp' => '0134455667', 'patient_email' => 'rohana@uitm.edu.my', 'patient_type' => 'STAFF', 'patient_meds_history' => 'Hypertension, takes medicine daily', 'ic_number' => '750315-10-1122', 'student_id' => null, 'gender' => 'F'],
            ['patient_id' => 2010987654, 'user_id' => 105, 'patient_name' => 'En. Kamal Ariffin', 'patient_gender' => 'M', 'patient_dob' => '1982-12-10', 'patient_hp' => '0129988776', 'patient_email' => 'kamal@uitm.edu.my', 'patient_type' => 'STAFF', 'patient_meds_history' => 'Allergic to Penicillin', 'ic_number' => '821210-10-3344', 'student_id' => null, 'gender' => 'M'],
            ['patient_id' => 2025101010, 'user_id' => 106, 'patient_name' => 'Haziq Bin Rosli', 'patient_gender' => 'M', 'patient_dob' => '2003-04-12', 'patient_hp' => '0112233445', 'patient_email' => 'haziq@student.uitm.edu.my', 'patient_type' => 'STUDENT', 'patient_meds_history' => 'G6PD Deficiency', 'ic_number' => '030412-10-5566', 'student_id' => '2025101010', 'gender' => 'M'],
            ['patient_id' => 2025998877, 'user_id' => 107, 'patient_name' => 'Stephen Raj A/L Muthu', 'patient_gender' => 'M', 'patient_dob' => '1985-06-20', 'patient_hp' => '0129988112', 'patient_email' => 'stephen@uitm.edu.my', 'patient_type' => 'STAFF', 'patient_meds_history' => 'Gastritis', 'ic_number' => '850620-10-7788', 'student_id' => null, 'gender' => 'M'],
            ['patient_id' => 2025202020, 'user_id' => 108, 'patient_name' => 'Jessica Wong', 'patient_gender' => 'F', 'patient_dob' => '2003-11-15', 'patient_hp' => '0165544332', 'patient_email' => 'jessica@student.uitm.edu.my', 'patient_type' => 'STUDENT', 'patient_meds_history' => 'No known allergies', 'ic_number' => '031115-10-9900', 'student_id' => '2025202020', 'gender' => 'F'],
            ['patient_id' => 2025333001, 'user_id' => 109, 'patient_name' => 'Sarah Binti Nasir', 'patient_gender' => 'F', 'patient_dob' => '2003-09-09', 'patient_hp' => '0113330011', 'patient_email' => 'sarah@student.uitm.edu.my', 'patient_type' => 'STUDENT', 'patient_meds_history' => 'Anxiety', 'ic_number' => '030909-10-1122', 'student_id' => '2025333001', 'gender' => 'F'],
            ['patient_id' => 2025444002, 'user_id' => 110, 'patient_name' => 'Raj Kumar', 'patient_gender' => 'M', 'patient_dob' => '2002-12-01', 'patient_hp' => '0124440022', 'patient_email' => 'raj@student.uitm.edu.my', 'patient_type' => 'STUDENT', 'patient_meds_history' => 'Fractured wrist 2020', 'ic_number' => '021201-10-3344', 'student_id' => '2025444002', 'gender' => 'M'],
            ['patient_id' => 2010777003, 'user_id' => 111, 'patient_name' => 'Pn. Salmah Bakar', 'patient_gender' => 'F', 'patient_dob' => '1978-02-28', 'patient_hp' => '0137770033', 'patient_email' => 'salmah@uitm.edu.my', 'patient_type' => 'STAFF', 'patient_meds_history' => 'Type 2 Diabetes', 'ic_number' => '780228-10-5566', 'student_id' => null, 'gender' => 'F'],
            ['patient_id' => 2015888004, 'user_id' => 112, 'patient_name' => 'En. Mat Nor', 'patient_gender' => 'M', 'patient_dob' => '1980-07-15', 'patient_hp' => '0198880044', 'patient_email' => 'matnor@uitm.edu.my', 'patient_type' => 'STAFF', 'patient_meds_history' => 'Heavy Smoker', 'ic_number' => '800715-10-7788', 'student_id' => null, 'gender' => 'M'],
            ['patient_id' => 2025999005, 'user_id' => 113, 'patient_name' => 'Mei Ling', 'patient_gender' => 'F', 'patient_dob' => '2004-01-20', 'patient_hp' => '0179990055', 'patient_email' => 'meiling@student.uitm.edu.my', 'patient_type' => 'STUDENT', 'patient_meds_history' => 'Sensitive Teeth', 'ic_number' => '040120-10-9900', 'student_id' => '2025999005', 'gender' => 'F'],
        ];
        foreach ($patients as $p) {
            Patient::updateOrCreate(['patient_id' => $p['patient_id']], $p);
        }

        // Medications
        $medications = [
            ['meds_id' => 6001, 'meds_name' => 'Paracetamol 500mg', 'meds_type' => 'Pill', 'stock_quantity' => 500],
            ['meds_id' => 6002, 'meds_name' => 'Amoxicillin 250mg', 'meds_type' => 'Capsule', 'stock_quantity' => 250],
            ['meds_id' => 6003, 'meds_name' => 'Difflam Lozenges', 'meds_type' => 'Lozenge', 'stock_quantity' => 150],
            ['meds_id' => 6004, 'meds_name' => 'Mefenamic Acid 250mg', 'meds_type' => 'Pill', 'stock_quantity' => 300],
            ['meds_id' => 6005, 'meds_name' => 'Chlorhexidine Mouthwash', 'meds_type' => 'Liquid', 'stock_quantity' => 75],
            ['meds_id' => 6006, 'meds_name' => 'Amlodipine 5mg', 'meds_type' => 'Pill', 'stock_quantity' => 200],
            ['meds_id' => 6007, 'meds_name' => 'Loratadine 10mg', 'meds_type' => 'Pill', 'stock_quantity' => 180],
            ['meds_id' => 6008, 'meds_name' => 'Gaviscon Liquid', 'meds_type' => 'Liquid', 'stock_quantity' => 100],
            ['meds_id' => 6009, 'meds_name' => 'Augmentin 625mg', 'meds_type' => 'Pill', 'stock_quantity' => 150],
            ['meds_id' => 6010, 'meds_name' => 'ORS Sachet', 'meds_type' => 'Powder', 'stock_quantity' => 200],
            ['meds_id' => 6011, 'meds_name' => 'Benzocaine Gel', 'meds_type' => 'Gel', 'stock_quantity' => 50],
        ];
        foreach ($medications as $m) {
            Medication::updateOrCreate(['meds_id' => $m['meds_id']], $m);
        }

        $this->seedAppointments();
        $this->seedMedicalCheckups();
        $this->seedVaccinations();
        $this->seedPrescribedMeds();
        $this->seedMedicalCertificates();

        $this->syncOracleSequence();
    }

    private function seedAppointments(): void
    {
        $appts = [
            [10001, '2024-01-10', '09:30 AM', 'DISCHARGED', 0.00, 'Student presented with high fever', 2023456789, 2010123456],
            [10002, '2024-01-12', '10:00 AM', 'DISCHARGED', 0.00, 'Staff monthly checkup', 2005123456, 2012987654],
            [10003, '2024-02-01', '02:00 PM', 'DISCHARGED', 50.00, 'Food handling requirement', 2022876543, 2020555001],
            [10004, '2024-02-15', '11:15 AM', 'DISCHARGED', 0.00, 'Severe pain in molar', 2024112233, 2014223344],
            [10005, '2025-12-01', '08:30 AM', 'PENDING', 0.00, 'General screening', 2010987654, 2015998877],
            [10006, '2025-12-02', '08:45 AM', 'DISCHARGED', 0.00, 'Severe stomach ache and vomiting', 2025101010, 2010123456],
            [10007, '2025-12-05', '10:30 AM', 'DISCHARGED', 0.00, 'Routine scaling and polishing', 2025998877, 2018334455],
            [10008, '2025-12-10', '02:15 PM', 'DISCHARGED', 0.00, 'Rashes after eating seafood', 2025202020, 2020555001],
            [10009, '2025-12-15', '09:00 AM', 'DISCHARGED', 35.00, 'Elective vaccination request', 2023456789, 2012987654],
            [10010, '2026-01-05', '11:00 AM', 'DISCHARGED', 0.00, 'Monthly BP review', 2005123456, 2012987654],
            [10011, '2026-02-01', '09:30 AM', 'PENDING', 0.00, 'Wisdom tooth consultation', 2025202020, 2016445566],
            [10012, '2025-12-03', '09:00 AM', 'DISCHARGED', 0.00, 'Sore throat and fever', 2023456789, 2010123456],
            [10013, '2025-12-03', '09:30 AM', 'DISCHARGED', 0.00, 'Coughing for 3 days', 2022876543, 2020555001],
            [10014, '2025-12-03', '10:00 AM', 'DISCHARGED', 0.00, 'High fever', 2025333001, 2012987654],
            [10015, '2025-12-04', '08:30 AM', 'DISCHARGED', 0.00, 'Regular Diabetes checkup', 2010777003, 2013567890],
            [10016, '2025-12-04', '02:00 PM', 'DISCHARGED', 0.00, 'Sprained ankle at gym', 2025444002, 2022777003],
            [10017, '2025-12-05', '11:00 AM', 'DISCHARGED', 0.00, 'Toothache lower jaw', 2025999005, 2016445566],
            [10018, '2025-12-06', '09:15 AM', 'DISCHARGED', 0.00, 'Gum bleeding', 2015888004, 2014223344],
            [10019, '2025-12-08', '10:30 AM', 'DISCHARGED', 35.00, 'Flu Vaccination', 2005123456, 2020555001],
            [10020, '2025-12-09', '03:00 PM', 'DISCHARGED', 0.00, 'Migraine headache', 2010987654, 2015998877],
            [10021, '2025-12-12', '08:45 AM', 'DISCHARGED', 0.00, 'Gastric pain', 2025333001, 2012987654],
            [10022, '2025-12-15', '09:30 AM', 'DISCHARGED', 0.00, 'Asthma attack mild', 2023456789, 2010123456],
            [10023, '2025-12-16', '11:15 AM', 'DISCHARGED', 0.00, 'Routine scaling', 2023456789, 2022111222],
            [10024, '2025-12-18', '10:00 AM', 'DISCHARGED', 0.00, 'Skin rash on arm', 2022876543, 2024999005],
            [10025, '2025-12-20', '02:30 PM', 'DISCHARGED', 0.00, 'Follow up wound dressing', 2025444002, 2022777003],
            [10026, '2025-12-22', '09:00 AM', 'DISCHARGED', 0.00, 'Chest pain (Referral)', 2015888004, 2010123456],
            [10027, '2025-12-23', '11:45 AM', 'CANCEL', 0.00, 'Patient did not show up', 2025999005, 2017778899],
            [10028, '2025-12-24', '08:30 AM', 'DISCHARGED', 0.00, 'Food poisoning', 2010777003, 2014112233],
            [10029, '2025-12-27', '10:15 AM', 'DISCHARGED', 0.00, 'Loose filling', 2025202020, 2018334455],
            [10030, '2025-12-29', '09:00 AM', 'DISCHARGED', 0.00, 'Insomnia / Stress', 2024112233, 2012987654],
            [10031, '2026-01-02', '08:30 AM', 'DISCHARGED', 0.00, 'Medical checkup for sports', 2025444002, 2010123456],
            [10032, '2026-01-03', '10:00 AM', 'DISCHARGED', 0.00, 'Diabetes Follow up', 2010777003, 2013567890],
            [10033, '2026-01-05', '11:30 AM', 'DISCHARGED', 0.00, 'Wisdom tooth pain', 2025333001, 2016445566],
            [10034, '2026-01-07', '09:45 AM', 'DISCHARGED', 0.00, 'Eye infection', 2010987654, 2023888004],
            [10035, '2026-01-10', '02:15 PM', 'DISCHARGED', 50.00, 'Typhoid Vaccine', 2025999005, 2020555001],
            [10036, '2026-01-12', '10:00 AM', 'DISCHARGED', 0.00, 'Hypertension Check', 2005123456, 2012987654],
            [10037, '2026-01-14', '03:30 PM', 'DISCHARGED', 0.00, 'Cut finger (Kitchen)', 2022876543, 2021666002],
            [10038, '2026-01-15', '09:00 AM', 'DISCHARGED', 0.00, 'Chronic back pain', 2015888004, 2015998877],
            [10039, '2026-01-18', '11:00 AM', 'DISCHARGED', 0.00, 'Scaling and polishing', 2025444002, 2022111222],
            [10040, '2026-01-20', '08:45 AM', 'DISCHARGED', 0.00, 'Fever and chills', 2025202020, 2010123456],
            [10041, '2026-01-22', '10:30 AM', 'DISCHARGED', 0.00, 'Dizziness', 2025333001, 2014112233],
            [10042, '2026-01-25', '02:00 PM', 'DISCHARGED', 0.00, 'Follow up root canal', 2025333001, 2016445566],
            [10043, '2026-01-26', '09:15 AM', 'DISCHARGED', 0.00, 'Acne consultation', 2023456789, 2013567890],
            [10044, '2026-01-28', '11:00 AM', 'DISCHARGED', 0.00, 'Sore throat', 2024112233, 2023888004],
            [10045, '2026-02-01', '09:00 AM', 'PENDING', 0.00, 'Monthly Diabetic Check', 2010777003, 2013567890],
            [10046, '2026-02-02', '10:00 AM', 'PENDING', 0.00, 'Braces consultation', 2022876543, 2017778899],
            [10047, '2026-02-03', '08:30 AM', 'PENDING', 0.00, 'Health screening', 2015888004, 2015998877],
            [10048, '2026-02-05', '02:00 PM', 'PENDING', 0.00, 'Follow up physio', 2025444002, 2010123456],
            [10049, '2026-02-10', '11:00 AM', 'PENDING', 35.00, 'Hep B Booster', 2025999005, 2020555001],
            [10050, '2026-02-12', '09:30 AM', 'PENDING', 0.00, 'General Checkup', 2025333001, 2012987654],
        ];
        foreach ($appts as $a) {
            Appointment::updateOrCreate(['appt_id' => $a[0]], ['appt_date' => $a[1], 'appt_time' => $a[2], 'appt_status' => $a[3], 'appt_payment' => $a[4], 'appt_note' => $a[5], 'patient_id' => $a[6], 'doctor_id' => $a[7]]);
        }
    }

    private function seedMedicalCheckups(): void
    {
        $checkups = [
            [10001, 'Headache, temp 39C, sore throat', 'Throat swab negative', 'Viral Fever', 'Prescribed antipyretics and rest'],
            [10002, 'Dizziness', 'BP Reading: 150/95', 'Uncontrolled Hypertension', 'Adjust dosage of Amlodipine'],
            [10004, 'Sharp pain in lower left molar', 'X-Ray', 'Deep caries on tooth 36', 'Temporary filling placed, schedule root canal'],
            [10006, 'Vomiting x3, Diarrhea, Dehydration', 'Palpation: Tender abdomen', 'Acute Gastroenteritis', 'IV Drip administered, observation for 2 hours'],
            [10007, 'Calculus buildup on lower incisors', 'Visual inspection', 'Gingivitis mild', 'Full mouth scaling and polishing done'],
            [10008, 'Itchy red hives on arms and neck', 'BP 120/80, Breathing normal', 'Mild Urticaria', 'Antihistamine injection given'],
            [10010, 'No complaints, routine check', 'BP: 130/85 (Controlled)', 'Hypertension - Stable', 'Continue current medication'],
            [10012, 'Pain when swallowing, temp 38.5C', 'Tonsils inflamed', 'Acute Tonsillitis', 'Prescribed Antibiotics and Lozenges'],
            [10013, 'Dry cough, no fever', 'Lungs clear', 'Viral upper respiratory infection', 'Symptomatic treatment'],
            [10015, 'Feeling thirsty often', 'Glucose Random: 11.5 mmol/L', 'Uncontrolled Diabetes', 'Increase insulin dosage, nutritional advice'],
            [10016, 'Swollen right ankle, pain 7/10', 'X-ray: No fracture', 'Grade 2 Ligament Sprain', 'RICE method, bandage, MC given'],
            [10017, 'Throbbing pain lower left', 'Cold test positive', 'Pulpitis reversible', 'Remove decay, temp filling'],
            [10020, 'Sensitivity to light, throbbing', 'Neuro exam normal', 'Migraine with aura', 'Rest in dark room, painkillers'],
            [10022, 'Wheezing, shortness of breath', 'Peak flow: 350', 'Mild Asthma exacerbation', 'Nebulizer given, continue inhaler'],
            [10028, 'Vomiting, stomach cramps', 'Dehydration signs', 'Food Poisoning', 'IV hydration, anti-emetics'],
            [10029, 'Filling dislodged while eating', 'Exam tooth 26', 'Lost Restoration', 'Replace filling (Composite)'],
            [10034, 'Redness in left eye, sticky discharge', 'Visual acuity normal', 'Bacterial Conjunctivitis', 'Antibiotic eye drops'],
            [10038, 'Lower back stiffness', 'Straight leg raise negative', 'Mechanical Back Pain', 'Physio referral, muscle relaxants'],
            [10040, 'High fever, body ache', 'Dengue rapid test negative', 'Viral Fever', 'Paracetamol, rest, fluids'],
        ];
        foreach ($checkups as $c) {
            MedicalCheckup::updateOrCreate(['appt_id' => $c[0]], ['checkup_symptom' => $c[1], 'checkup_test' => $c[2], 'checkup_finding' => $c[3], 'checkup_treatment' => $c[4]]);
        }
    }

    private function seedVaccinations(): void
    {
        $vaccs = [
            [10003, 'Typhoid', '2027-02-01', 'Typhim Vi vaccine given. No adverse reaction observed for 15 mins.'],
            [10009, 'Hepatitis B', '2035-12-15', 'Engerix-B Dose 1. Next dose scheduled in 1 month.'],
            [10019, 'Influenza (Quadrivalent)', '2026-12-08', 'Vaxigrip Tetra given on left deltoid.'],
            [10035, 'Typhoid', '2029-01-10', 'Typhim Vi given. Valid for 3 years.'],
        ];
        foreach ($vaccs as $v) {
            Vaccination::updateOrCreate(['appt_id' => $v[0]], ['vacc_for' => $v[1], 'vacc_exp_date' => $v[2], 'vacc_desc' => $v[3]]);
        }
    }

    private function seedPrescribedMeds(): void
    {
        $meds = [
            [1, '20 pills', '2 pills every 6 hours', 10001, 6001],
            [2, '6 lozenge', '1 lozenge as needed for throat pain', 10001, 6003],
            [3, '30 pills', '1 pill every morning', 10002, 6006],
            [4, '10 pills', '1 pills 3 times a day after eating', 10004, 6004],
            [5, '1 bottle 500ml', 'Gargle 1 cup twice daily', 10004, 6005],
            [6, '5 sachets', 'Mix 1 sachet in water after every purging', 10006, 6010],
            [7, '10 pills', '2 pills if fever > 38C', 10006, 6001],
            [8, '1 bottle 250ml', 'Gargle twice daily for 3 days', 10007, 6005],
            [9, '1 strip (10 pills)', '1 pill once a day at night', 10008, 6007],
            [10, '60 pills', '1 pill every morning (2 months supply)', 10010, 6006],
            [11, '15 caps', '1 cap 3 times a day', 10012, 6002],
            [12, '1 pack', 'Take as needed', 10012, 6003],
            [13, '10 pills', '1 pill for pain', 10016, 6004],
            [14, '10 pills', '1 pill nightly', 10022, 6007],
            [15, '5 sachets', 'Mix with water', 10028, 6010],
            [16, '1 bottle', '10ml before meals', 10028, 6008],
            [17, '20 pills', '2 pills every 6 hours', 10040, 6001],
        ];
        foreach ($meds as $m) {
            PrescribedMed::updateOrCreate(['prescribe_id' => $m[0]], ['amount' => $m[1], 'dosage' => $m[2], 'appt_id' => $m[3], 'meds_id' => $m[4]]);
        }
    }

    private function seedMedicalCertificates(): void
    {
        $mcs = [
            [1, '2024-01-10', '2024-01-11', 10001],
            [2, '2024-02-15', '2024-02-15', 10004],
            [3, '2025-12-02', '2025-12-03', 10006],
            [4, '2025-12-10', '2025-12-10', 10008],
            [5, '2025-12-03', '2025-12-04', 10012],
            [6, '2025-12-03', '2025-12-05', 10014],
            [7, '2025-12-04', '2025-12-08', 10016],
            [8, '2025-12-09', '2025-12-09', 10020],
            [9, '2025-12-24', '2025-12-25', 10028],
            [10, '2026-01-07', '2026-01-08', 10034],
        ];
        foreach ($mcs as $mc) {
            MedicalCertificate::updateOrCreate(['mc_id' => $mc[0]], ['mc_date_start' => $mc[1], 'mc_date_end' => $mc[2], 'appt_id' => $mc[3]]);
        }
    }

    private function syncOracleSequence(): void
    {
        try {
            $maxId = User::max('id');
            $nextId = $maxId + 1;
            // Reset the sequence to the next available ID
            \Illuminate\Support\Facades\DB::statement("DROP SEQUENCE users_id_seq");
            \Illuminate\Support\Facades\DB::statement("CREATE SEQUENCE users_id_seq START WITH {$nextId}");
        } catch (\Exception $e) {
            // Silently fail or log if sequence doesn't exist or other error
            try {
                $maxId = User::max('id');
                $nextId = $maxId + 1;
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY (id GENERATED AS IDENTITY (START WITH {$nextId}))");
            } catch (\Exception $e2) {
                // ignore
            }
        }
    }
}
