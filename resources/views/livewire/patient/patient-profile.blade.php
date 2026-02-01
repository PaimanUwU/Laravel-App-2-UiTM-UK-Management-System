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

    <div class="space-y-6">
        <div>
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button wire:click="$set('activeTab', 'overview')"
                        class="{{ $activeTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Overview
                    </button>

                    <button wire:click="$set('activeTab', 'history')"
                        class="{{ $activeTab === 'history' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Medical History
                    </button>

                    <button wire:click="$set('activeTab', 'prescriptions')"
                        class="{{ $activeTab === 'prescriptions' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Prescriptions
                    </button>
                </nav>
            </div>
        </div>

        @if ($activeTab === 'overview')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Vitals Card (Placeholder for now) -->
                <flux:card class="p-4 md:col-span-1 space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon.heart variant="mini" class="text-red-500" />
                        <h3 class="font-semibold">Recent Vitals</h3>
                    </div>
                    <div class="p-4 grid grid-cols-2 gap-4">
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
                <flux:card class="p-4 md:col-span-2 space-y-4">
                    <h3 class="font-semibold mb-2">Demographic Details</h3>
                    <div class="p-4 grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
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
        @endif

        @if ($activeTab === 'history')
            <div class="text-center py-12 text-gray-600">
                <flux:icon.clipboard-document-list class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>No consultation history found.</p>
            </div>
        @endif

        @if ($activeTab === 'prescriptions')
            <div class="text-center py-12 text-gray-600">
                <flux:icon.beaker class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>No active prescriptions.</p>
            </div>
        @endif
    </div>
</div>
