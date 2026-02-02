<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    public $user;
    public $patient;

    public $profileForm = [
        'name' => '',
        'email' => '',
        'patient_gender' => '',
        'patient_dob' => '',
        'patient_hp' => '',
        'phone' => '',
        'address' => '',
        'student_id' => '',
        'ic_number' => '',
        'patient_meds_history' => '',
    ];

    public $passwordForm = [
        'current_password' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public function mount()
    {
        $this->user = Auth::user();
        $this->patient = $this->user->patient;

        $this->profileForm = [
            'name' => $this->user->name,
            'email' => $this->user->email,
        ];

        if ($this->patient) {
            $this->profileForm = array_merge($this->profileForm, [
                'patient_gender' => $this->patient->patient_gender,
                'patient_dob' => $this->patient->patient_dob,
                'patient_hp' => $this->patient->patient_hp,
                'phone' => $this->patient->phone,
                'address' => $this->patient->address,
                'student_id' => $this->patient->student_id,
                'ic_number' => $this->patient->ic_number,
                'patient_meds_history' => $this->patient->patient_meds_history,
            ]);
        }
    }

    public function updateProfile()
    {
        $this->validate([
            'profileForm.name' => 'required|string|max:255',
            'profileForm.email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email,' . $this->user->id . ',id',
            ],
        ]);

        // Update user account
        $this->user->update([
            'name' => $this->profileForm['name'],
            'email' => $this->profileForm['email'],
        ]);

        // Update patient profile if exists
        if ($this->patient) {
            $this->validate([
                'profileForm.patient_gender' => 'required|in:MALE,FEMALE,OTHER',
                'profileForm.patient_dob' => 'required|date|before:today',
                'profileForm.patient_hp' => 'nullable|string|max:20',
                'profileForm.phone' => 'nullable|string|max:20',
                'profileForm.address' => 'nullable|string|max:500',
                'profileForm.student_id' => 'nullable|string|max:50',
                'profileForm.ic_number' => 'nullable|string|max:20',
                'profileForm.patient_meds_history' => 'nullable|string|max:1000',
            ]);

            $this->patient->update([
                'patient_name' => $this->profileForm['name'],
                'patient_email' => $this->profileForm['email'],
                'patient_gender' => $this->profileForm['patient_gender'],
                'patient_dob' => $this->profileForm['patient_dob'],
                'patient_hp' => $this->profileForm['patient_hp'],
                'phone' => $this->profileForm['phone'],
                'address' => $this->profileForm['address'],
                'student_id' => $this->profileForm['student_id'],
                'ic_number' => $this->profileForm['ic_number'],
                'patient_meds_history' => $this->profileForm['patient_meds_history'],
            ]);
        }

        \Flux::toast('Profile updated successfully.');
    }

    public function updatePassword()
    {
        $this->validate([
            'passwordForm.current_password' => 'required',
            'passwordForm.password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($this->passwordForm['current_password'], $this->user->password)) {
            $this->addError('passwordForm.current_password', 'The current password is incorrect.');
            return;
        }

        $this->user->update([
            'password' => Hash::make($this->passwordForm['password']),
        ]);

        $this->reset('passwordForm');
        \Flux::toast('Password updated successfully.');
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
            <p class="text-gray-600">Manage your personal information and preferences</p>
        </div>
        <flux:badge size="lg" color="blue">
            {{ $user->getRoleNames()->first() ?? 'User' }}
        </flux:badge>
    </div>

    <!-- Profile Information -->
    <flux:card>
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                <flux:icon.user variant="solid" class="w-6 h-6" />
            </div>
            <h2 class="text-xl font-bold">Profile Information</h2>
        </div>

        <form wire:submit.prevent="updateProfile" class="space-y-4">
            <!-- Basic Information (for all users) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input
                    label="Full Name"
                    wire:model="profileForm.name"
                    required
                />
                <flux:input
                    label="Email Address"
                    type="email"
                    wire:model="profileForm.email"
                    required
                />
            </div>

            <!-- Patient Information (only for patients) -->
            @if($user->hasRole('patient') && $patient)
                <div class="border-t pt-4 mt-6">
                    <h3 class="text-lg font-semibold mb-4">Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:select
                            label="Gender"
                            wire:model="profileForm.patient_gender"
                            required
                        >
                            <option value="">Select Gender</option>
                            <option value="MALE">Male</option>
                            <option value="FEMALE">Female</option>
                            <option value="OTHER">Other</option>
                        </flux:select>
                        <flux:input
                            label="Date of Birth"
                            type="date"
                            wire:model="profileForm.patient_dob"
                            required
                        />
                        <flux:input
                            label="Phone Number"
                            wire:model="profileForm.patient_hp"
                            placeholder="+60123456789"
                        />
                        <flux:input
                            label="Alternative Phone"
                            wire:model="profileForm.phone"
                            placeholder="+60123456789"
                        />
                        <flux:input
                            label="Student ID"
                            wire:model="profileForm.student_id"
                            placeholder="e.g., 2021234567"
                        />
                        <flux:input
                            label="IC Number"
                            wire:model="profileForm.ic_number"
                            placeholder="e.g., 123456-78-9012"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-4 mt-4">
                        <flux:textarea
                            label="Address"
                            wire:model="profileForm.address"
                            rows="3"
                            placeholder="Enter your full address"
                        />

                        <flux:textarea
                            label="Medical History"
                            wire:model="profileForm.patient_meds_history"
                            rows="4"
                            placeholder="List any known allergies, chronic conditions, or medications..."
                        />
                    </div>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">
                    <flux:icon.check variant="mini" class="w-4 h-4 mr-2" />
                    Update Profile
                </flux:button>
            </div>
        </form>
    </flux:card>

    <!-- Password Change -->
    <flux:card>
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-red-50 text-red-600 rounded-lg">
                <flux:icon.lock-closed variant="solid" class="w-6 h-6" />
            </div>
            <h2 class="text-xl font-bold">Change Password</h2>
        </div>

        <form wire:submit.prevent="updatePassword" class="space-y-4">
            <flux:input
                label="Current Password"
                type="password"
                wire:model="passwordForm.current_password"
                required
            />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input
                    label="New Password"
                    type="password"
                    wire:model="passwordForm.password"
                    required
                    helper="Must be at least 8 characters"
                />
                <flux:input
                    label="Confirm New Password"
                    type="password"
                    wire:model="passwordForm.password_confirmation"
                    required
                />
            </div>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">
                    <flux:icon.lock-closed variant="mini" class="w-4 h-4 mr-2" />
                    Update Password
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
