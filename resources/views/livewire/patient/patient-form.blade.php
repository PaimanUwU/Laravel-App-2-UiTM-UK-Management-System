<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

new class extends Component {
    public ?Patient $patient = null;

    // Form fields
    public $name = '';
    public $ic_number = '';
    public $student_id = '';
    public $email = '';
    public $gender = '';
    public $date_of_birth = '';
    public $phone = '';
    public $address = '';
    public $type = 'STUDENT';

    public function mount(?Patient $patient = null)
    {
        if ($patient && $patient->exists) {
            $this->patient = $patient;
            $this->name = $patient->patient_name;
            $this->ic_number = $patient->ic_number;
            $this->student_id = $patient->student_id;
            $this->email = $patient->patient_email;
            $this->gender = $patient->patient_gender;
            $this->date_of_birth = $patient->patient_dob;
            $this->phone = $patient->patient_hp;
            $this->address = $patient->address;
            $this->type = $patient->patient_type ?? 'STUDENT';
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'ic_number' => ['required', 'string', 'max:20', Rule::unique('patients')->ignore($this->patient?->patient_id, 'patient_id')],
            'student_id' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gender' => 'required|in:M,F',
            'date_of_birth' => 'required|date',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'type' => 'required|in:STUDENT,STAFF',
        ]);

        if ($this->patient) {
            $this->patient->update([
                'patient_name' => $this->name,
                'ic_number' => $this->ic_number,
                'student_id' => $this->student_id,
                'patient_email' => $this->email,
                'patient_gender' => $this->gender,
                'patient_dob' => $this->date_of_birth,
                'patient_hp' => $this->phone,
                'address' => $this->address,
                'patient_type' => $this->type,
            ]);
            $action = 'update_patient';
            $description = "Updated patient: {$this->name} ({$this->ic_number})";
        } else {
            // Create user account for patient portal access
            // Uses IC as default password
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email ?? $this->ic_number . '@uitm.edu.my', // Fallback email
                'password' => Hash::make($this->ic_number),
            ]);
            $user->assignRole('patient');

            $this->patient = Patient::create([
                'user_id' => $user->id,
                'patient_name' => $this->name,
                'ic_number' => $this->ic_number,
                'student_id' => $this->student_id,
                'patient_email' => $this->email,
                'patient_gender' => $this->gender,
                'patient_dob' => $this->date_of_birth,
                'patient_hp' => $this->phone,
                'address' => $this->address,
                'patient_type' => $this->type,
            ]);

            $action = 'create_patient';
            $description = "Registered new patient: {$this->name} ({$this->ic_number})";
        }

        // Log action
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('patients.index')
            ->with('status', 'Patient saved successfully.');
    }
};
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <flux:button variant="ghost" href="{{ route('patients.index') }}" icon="chevron-left" class="-ml-2 mb-4">
            Back to Repository
        </flux:button>
        <h1 class="text-2xl font-bold text-gray-900">{{ $patient ? 'Edit Patient' : 'Register New Patient' }}</h1>
        <p class="text-sm text-gray-600">Enter demographic details for the student or staff member.</p>
    </div>

    <flux:card>
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="col-span-1 md:col-span-2">
                <h3 class="text-lg font-medium mb-4">Personal Information</h3>
            </div>

            <flux:input wire:model="name" label="Full Name" placeholder="e.g. Ahmad Albab" />

            <flux:select wire:model="type" label="Patient Type">
                <flux:select.option value="STUDENT">Student</flux:select.option>
                <flux:select.option value="STAFF">Staff</flux:select.option>
            </flux:select>

            <flux:input wire:model="ic_number" label="IC Number" placeholder="e.g. 981212105555" />

            <flux:input wire:model="student_id" label="Student/Staff ID" placeholder="e.g. 2023123456" />

            <flux:radio.group wire:model="gender" label="Gender" class="flex gap-6">
                <flux:radio value="M" label="Male" />
                <flux:radio value="F" label="Female" />
            </flux:radio.group>

            <flux:input wire:model="date_of_birth" type="date" label="Date of Birth" />

            <div class="col-span-1 md:col-span-2 pt-4 border-t border-gray-100">
                <h3 class="text-lg font-medium mb-4">Contact Details</h3>
            </div>

            <flux:input wire:model="email" type="email" label="Email Address" />

            <flux:input wire:model="phone" label="Phone Number" placeholder="e.g. 012-3456789" />

            <div class="col-span-1 md:col-span-2">
                <flux:textarea wire:model="address" label="Address"
                    placeholder="Enter home address or college residence" />
            </div>

            <div class="col-span-1 md:col-span-2 flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                <flux:button href="{{ route('patients.index') }}" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary">{{ $patient ? 'Update Record' : 'Register Patient' }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>