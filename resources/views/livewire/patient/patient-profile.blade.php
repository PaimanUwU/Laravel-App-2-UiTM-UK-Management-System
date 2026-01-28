<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use Livewire\WithPagination;

new class extends Component {
    public Patient $patient;
    public $activeTab = 'overview';

    public function mount(Patient $patient)
    {
        $this->patient = $patient;
    }
};
?>

<div class="space-y-6">
    <div>
        <flux:button variant="ghost" href="{{ route('patients.index') }}" icon="chevron-left" class="-ml-2 mb-4">
            Back to Repository
        </flux:button>

        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $patient->patient_name }}</h1>
                <div class="flex items-center gap-3 mt-2 text-sm text-gray-600">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded">{{ $patient->ic_number }}</span>
                    <span>•</span>
                    <span>{{ $patient->student_id }}</span>
                    <span>•</span>
                    <flux:badge color="{{ $patient->patient_type === 'STAFF' ? 'purple' : 'blue' }}" size="sm">
                        {{ $patient->patient_type ?? 'STUDENT' }}
                    </flux:badge>
                </div>
            </div>
            <flux:button variant="primary" icon="pencil-square"
                href="{{ route('patients.edit', $patient->patient_id) }}">
                Edit Profile
            </flux:button>
        </div>
    </div>

    <flux:tab.group>
        <flux:tabs wire:model="activeTab">
            <flux:tab name="overview">Overview</flux:tab>
            <flux:tab name="history">Medical History</flux:tab>
            <flux:tab name="prescriptions">Prescriptions</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="overview">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Vitals Card (Placeholder for now) -->
                <flux:card class="md:col-span-1 space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon.heart variant="mini" class="text-red-500" />
                        <h3 class="font-semibold">Recent Vitals</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-gray-600">Blood Pressure</div>
                            <div class="font-mono font-medium">--/--</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Heart Rate</div>
                            <div class="font-mono font-medium">-- bpm</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Weight</div>
                            <div class="font-mono font-medium">-- kg</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Height</div>
                            <div class="font-mono font-medium">-- cm</div>
                        </div>
                    </div>
                </flux:card>

                <!-- Demographics -->
                <flux:card class="md:col-span-2 space-y-4">
                    <h3 class="font-semibold mb-2">Demographic Details</h3>
                    <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
                        <div>
                            <span class="text-gray-600 block">Email</span>
                            <span class="font-medium">{{ $patient->patient_email }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 block">Phone</span>
                            <span class="font-medium">{{ $patient->patient_hp }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 block">Gender</span>
                            <span class="font-medium">{{ $patient->patient_gender === 'M' ? 'Male' : 'Female' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 block">Date of Birth</span>
                            <span
                                class="font-medium">{{ \Carbon\Carbon::parse($patient->patient_dob)->format('d M Y') }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-600 block">Address</span>
                            <span class="font-medium">{{ $patient->address }}</span>
                        </div>
                    </div>
                </flux:card>
            </div>
        </flux:tab.panel>

        <flux:tab.panel name="history">
            <div class="text-center py-12 text-gray-600">
                <flux:icon.clipboard-document-list class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>No consultation history found.</p>
            </div>
        </flux:tab.panel>

        <flux:tab.panel name="prescriptions">
            <div class="text-center py-12 text-gray-600">
                <flux:icon.beaker class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>No active prescriptions.</p>
            </div>
        </flux:tab.panel>
    </flux:tab.group>
</div>