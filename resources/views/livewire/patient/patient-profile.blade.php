<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\Appointment;
use Livewire\WithPagination;
use App\Models\MedicalCertificate;
use App\Models\StockMovement;
use App\Models\Medication;
use App\Models\PrescribedMed;
use App\Models\MedicalCheckup;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public Patient $patient;
    public $activeTab = 'overview';
    public $isPatient = false;

    // Helper functions
    private function getCurrentDoctor()
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }
        
        // If user is admin, return null (no doctor profile)
        if ($user->hasRole('system_admin')) {
            return null;
        }
        
        // Try to get the doctor profile
        return $user->doctor;
    }

    private function hasDoctorProfile()
    {
        return $this->getCurrentDoctor() !== null;
    }

    // Current Appointment Context
    public $currentApptId = null;


    public function mount(Patient $patient)
    {
        $this->patient = $patient;
        $user = auth()->user();

        // Determine role
        $this->isPatient = $user->patient && !$user->hasAnyRole(['system_admin', 'doctor', 'staff', 'head_office']);

        // Check permission
        if ($this->isPatient && $user->patient->patient_id !== $patient->patient_id) {
            abort(403, 'Unauthorized access to patient profile.');
        }

        // Handle query param for tab
        if (request()->query('tab') === 'history') {
            $this->activeTab = 'history';
        } elseif (request()->query('tab') === 'consultation') {
            $this->activeTab = 'consultation';
        }

        // Load Medications for Dropdown
        $this->availableMeds = Medication::all();

        // Handle existing appointment context
        if (request()->query('appt_id')) {
            $this->loadAppointmentContext(request()->query('appt_id'));
        }

        $this->loadData();
    }

    public $latestCheckupId = null;
    public $vital_bp = '';
    public $vital_heart_rate = '';
    public $vital_weight = '';
    public $vital_height = '';

    // Consultation Form - Vitals
    public $cons_vital_bp = '';
    public $cons_vital_heart_rate = '';
    public $cons_vital_weight = '';
    public $cons_vital_height = '';

    // Consultation Form - Details
    public $consultation_symptom = '';
    public $consultation_finding = '';
    public $consultation_treatment = '';
    public $consultation_notes = '';

    // Consultation Form - Prescriptions (Repeater Style)
    public $availableMeds = [];
    public $selectedMeds = []; // Array of ['meds_id', 'amount', 'dosage']

    // Consultation Form - MC
    public $mc_start_date = '';
    public $mc_days = '';

    public function loadAppointmentContext($apptId)
    {
        $appt = Appointment::where('appt_id', $apptId)
            ->where('patient_id', $this->patient->patient_id)
            ->first();

        if ($appt) {
            $this->currentApptId = $appt->appt_id;
            $this->consultation_notes = $appt->appt_note;

            if ($appt->medicalCheckup) {
                $this->consultation_symptom = $appt->medicalCheckup->checkup_symptom;
                $this->consultation_finding = $appt->medicalCheckup->checkup_finding;
                $this->consultation_treatment = $appt->medicalCheckup->checkup_treatment;
                $this->cons_vital_bp = $appt->medicalCheckup->vital_bp;
                $this->cons_vital_heart_rate = $appt->medicalCheckup->vital_heart_rate;
                $this->cons_vital_weight = $appt->medicalCheckup->vital_weight;
                $this->cons_vital_height = $appt->medicalCheckup->vital_height;
            }

            // Load Prescriptions into Repeater Array
            $this->selectedMeds = $appt->prescribedMeds->map(function ($pm) {
                return [
                    'meds_id' => $pm->meds_id,
                    'amount' => $pm->amount,
                    'dosage' => $pm->dosage,
                ];
            })->toArray();

            $mc = MedicalCertificate::where('appt_id', $appt->appt_id)->first();
            if ($mc) {
                $this->mc_start_date = $mc->mc_date_start;
                $this->mc_days = \Carbon\Carbon::parse($mc->mc_date_start)->diffInDays($mc->mc_date_end) + 1;
            }
        }
    }


    public function addMedicationRow()
    {
        $this->selectedMeds[] = [
            'meds_id' => '',
            'amount' => 1,
            'dosage' => '1 tab 3x daily'
        ];
    }

    public function removeMedicationRow($index)
    {
        unset($this->selectedMeds[$index]);
        $this->selectedMeds = array_values($this->selectedMeds);
    }

    public function saveConsultation()
    {
        $this->validate([
            'consultation_symptom' => 'required|string',
            'mc_days' => 'nullable|numeric|min:0',
            'selectedMeds.*.meds_id' => 'required',
            'selectedMeds.*.amount' => 'required|numeric|min:1',
        ]);

        // Stock Validation
        foreach ($this->selectedMeds as $index => $med) {
            $medication = Medication::find($med['meds_id']);
            if ($medication && $medication->stock_quantity < $med['amount']) {
                $this->addError("selectedMeds.{$index}.amount", "Insufficient stock. Available: {$medication->stock_quantity}");
                return;
            }
        }

        DB::transaction(function () {
            // 1. Resolve Appointment
            if ($this->currentApptId) {
                $appt = Appointment::find($this->currentApptId);
                $appt->update([
                    'appt_status' => 'COMPLETED',
                    'appt_note' => $this->consultation_notes,
                ]);
            } else {
                $user = \Illuminate\Support\Facades\Auth::user();
                $doctor = $this->getCurrentDoctor();

                // Only doctors can create completed consultations
                if (!$doctor) {
                    session()->flash('error', 'Only doctors can create completed consultations.');
                    return;
                }

                $appt = Appointment::create([
                    'patient_id' => $this->patient->patient_id,
                    'doctor_id' => $doctor->doctor_id,
                    'appt_date' => now(),
                    'appt_time' => now()->format('h:i A'),
                    'appt_status' => 'Completed',
                    'appt_note' => $this->consultation_notes,
                    'appt_payment' => 0,
                ]);
            }

            // 2. Checkup
            MedicalCheckup::updateOrCreate(
                ['appt_id' => $appt->appt_id],
                [
                    'checkup_symptom' => $this->consultation_symptom,
                    'checkup_finding' => $this->consultation_finding,
                    'checkup_treatment' => $this->consultation_treatment,
                    'vital_bp' => $this->cons_vital_bp,
                    'vital_heart_rate' => $this->cons_vital_heart_rate,
                    'vital_weight' => $this->cons_vital_weight,
                    'vital_height' => $this->cons_vital_height,
                ]
            );

            // 3. Prescriptions & Stock
            $existingPrescriptions = PrescribedMed::where('appt_id', $appt->appt_id)->get();
            foreach ($existingPrescriptions as $prev) {
                $med = Medication::find($prev->meds_id);
                if ($med) {
                    $med->increment('stock_quantity', $prev->amount);
                }
                $prev->delete();
            }

            foreach ($this->selectedMeds as $medItem) {
                $medication = Medication::find($medItem['meds_id']);
                if ($medication) {
                    $medication->decrement('stock_quantity', $medItem['amount']);

                    StockMovement::create([
                        'meds_id' => $medication->meds_id,
                        'quantity' => $medItem['amount'],
                        'type' => 'OUT',
                        'reason' => 'Prescription Appt #' . $appt->appt_id,
                        'user_id' => auth()->id(),
                    ]);

                    PrescribedMed::create([
                        'appt_id' => $appt->appt_id,
                        'meds_id' => $medItem['meds_id'],
                        'amount' => $medItem['amount'],
                        'dosage' => $medItem['dosage'],
                    ]);
                }
            }

            // 4. MC
            if ($this->mc_start_date && $this->mc_days > 0) {
                MedicalCertificate::updateOrCreate(
                    ['appt_id' => $appt->appt_id],
                    [
                        'mc_date_start' => $this->mc_start_date,
                        'mc_date_end' => \Carbon\Carbon::parse($this->mc_start_date)->addDays($this->mc_days - 1),
                    ]
                );
            }
        });

        $this->reset([
            'consultation_symptom',
            'consultation_finding',
            'consultation_treatment',
            'consultation_notes',
            'cons_vital_bp',
            'cons_vital_heart_rate',
            'cons_vital_weight',
            'cons_vital_height',
            'mc_start_date',
            'mc_days',
            'currentApptId'
        ]);
        $this->selectedMeds = [];

        $this->loadData();
        $this->activeTab = 'history';
        \Flux::toast('Consultation saved successfully.');
    }

    public function loadData()
    {
        $latestAppt = $this->patient->appointments()
            ->whereHas('medicalCheckup')
            ->orderByDesc('appt_id')
            ->first();

        if ($latestAppt && $latestAppt->medicalCheckup) {
            $checkup = $latestAppt->medicalCheckup;
            $this->latestCheckupId = $checkup->appt_id;
            $this->vital_bp = $checkup->vital_bp;
            $this->vital_heart_rate = $checkup->vital_heart_rate;
            $this->vital_weight = $checkup->vital_weight;
            $this->vital_height = $checkup->vital_height;
        }
    }

    public function getHistoryProperty()
    {
        return $this->patient->appointments()->with(['medicalCheckup', 'doctor'])->whereHas('medicalCheckup')->latest('appt_date')->get();
    }
    public function getPrescriptionsProperty()
    {
        return $this->patient->appointments()->with('prescribedMeds.medication')->get()
            ->flatMap(fn($appt) => $appt->prescribedMeds)
            ->groupBy(fn($pm) => $pm->medication->meds_name)
            ->map(function ($group) {
                return ['name' => $group->first()->medication->meds_name, 'type' => $group->first()->medication->meds_type, 'count' => $group->count(), 'total_amount' => $group->count() . ' scripts', 'last_prescribed' => $group->max('created_at')];
            });
    }
    public function updateVitals()
    {
        if (!$this->latestCheckupId)
            return;
        $checkup = \App\Models\MedicalCheckup::where('appt_id', $this->latestCheckupId)->first();
        if ($checkup)
            $checkup->update(['vital_bp' => $this->vital_bp, 'vital_heart_rate' => $this->vital_heart_rate, 'vital_weight' => $this->vital_weight, 'vital_height' => $this->vital_height]);
        \Flux::toast('Vitals updated successfully.');
    }
};
?>

<div class="space-y-6">
    <div>
        @if(!$isPatient)
            <flux:button variant="ghost" href="{{ route('patients.index') }}" icon="chevron-left" class="-ml-2 mb-4">
                Back to Repository
            </flux:button>
        @else
            <flux:button variant="ghost" href="{{ route('dashboard') }}" icon="chevron-left" class="-ml-2 mb-4">
                Back to Dashboard
            </flux:button>
        @endif

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
            @if(!$isPatient)
                <flux:button variant="primary" icon="pencil-square"
                    href="{{ route('patients.edit', $patient->patient_id) }}">
                    Edit Profile
                </flux:button>
            @endif
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
                    @if(!$isPatient)
                        <button wire:click="$set('activeTab', 'consultation')"
                            class="{{ $activeTab === 'consultation' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            New Consultation
                        </button>
                    @endif
                </nav>
            </div>
        </div>

        @if ($activeTab === 'overview')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Vitals Card -->
                <flux:card class="p-4 md:col-span-1 space-y-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <flux:icon.heart variant="mini" class="text-red-500" />
                            <h3 class="font-semibold">Recent Vitals</h3>
                        </div>
                        @if(!$isPatient)
                            <flux:button size="xs" wire:click="updateVitals">Save</flux:button>
                        @endif
                    </div>
                    <div class="p-4 grid grid-cols-1 gap-4">
                        <flux:input label="Blood Pressure" placeholder="e.g. 120/80" wire:model="vital_bp"
                            :disabled="$isPatient" />
                        <flux:input label="Heart Rate (bpm)" type="number" placeholder="e.g. 72"
                            wire:model="vital_heart_rate" :disabled="$isPatient" />
                        <flux:input label="Weight (kg)" type="number" step="0.1" placeholder="e.g. 70.5"
                            wire:model="vital_weight" :disabled="$isPatient" />
                        <flux:input label="Height (cm)" type="number" step="0.1" placeholder="e.g. 175"
                            wire:model="vital_height" :disabled="$isPatient" />
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
            <div class="space-y-4">
                @forelse ($this->history as $appt)
                    <flux:card class="p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-semibold text-gray-900">
                                    {{ \Carbon\Carbon::parse($appt->appt_date)->format('d M Y') }}
                                    <span class="text-gray-500 font-normal text-sm">at {{ $appt->appt_time }}</span>
                                </div>
                                <div class="text-sm text-indigo-600 mt-1">
                                    {{ $appt->doctor->doctor_name ?? 'Unknown Doctor' }}
                                </div>
                            </div>
                            <flux:button size="sm" href="{{ route('consultations.view', ['appointment' => $appt->appt_id]) }}">
                                View Details
                            </flux:button>
                        </div>

                        @if ($appt->medicalCheckup)
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Diagnosis/Findings:</span>
                                    <p class="text-gray-600 line-clamp-2">{{ $appt->medicalCheckup->checkup_finding ?? '-' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Treatment:</span>
                                    <p class="text-gray-600 line-clamp-2">{{ $appt->medicalCheckup->checkup_treatment ?? '-' }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic mt-2">No medical checkup data recorded.</p>
                        @endif
                    </flux:card>
                @empty
                    <div class="text-center py-12 text-gray-600">
                        <flux:icon.clipboard-document-list class="w-12 h-12 mx-auto mb-3 opacity-50" />
                        <p>No consultation history found.</p>
                    </div>
                @endforelse
            </div>
        @endif

        @if ($activeTab === 'prescriptions')
            <div class="overflow-hidden bg-white sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                Medication</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Scripts
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Last
                                Prescribed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($this->prescriptions as $med)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    {{ $med['name'] }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $med['type'] }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $med['count'] }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $med['last_prescribed']->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500">
                                    <flux:icon.beaker class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                    No active prescriptions.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        @if ($activeTab === 'consultation')
            <form wire:submit.prevent="saveConsultation" class="space-y-6">
                <!-- Vitals Section -->
                <flux:card class="p-4 space-y-4">
                    <h3 class="font-semibold text-lg">Vitals</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <flux:input label="Blood Pressure" placeholder="e.g. 120/80" wire:model="cons_vital_bp" />
                        <flux:input label="Heart Rate (bpm)" type="number" wire:model="cons_vital_heart_rate" />
                        <flux:input label="Weight (kg)" type="number" step="0.1" wire:model="cons_vital_weight" />
                        <flux:input label="Height (cm)" type="number" step="0.1" wire:model="cons_vital_height" />
                    </div>
                </flux:card>

                <!-- Consultation Details -->
                <flux:card class="p-4 space-y-4">
                    <h3 class="font-semibold text-lg">Consultation Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:textarea label="Symptoms & Complaints" rows="3" wire:model="consultation_symptom" />
                        <flux:textarea label="Clinical Findings/Diagnosis" rows="3" wire:model="consultation_finding" />
                        <flux:textarea label="Doctor's Remarks/Treatment" rows="3" wire:model="consultation_treatment" />
                        <flux:textarea label="Notes" rows="3" wire:model="consultation_notes" />
                    </div>
                </flux:card>

                <!-- Prescriptions (Repeater Style) -->
                <flux:card class="p-4 space-y-6">
                    <h3 class="font-semibold text-lg">Prescriptions</h3>

                    <div class="space-y-4">
                        @foreach ($selectedMeds as $index => $med)
                            <div class="p-4 border rounded-lg bg-gray-50 relative flex flex-col md:flex-row gap-4 items-end">
                                <div class="flex-1 ">
                                    <flux:label>Medication</flux:label>
                                    <select wire:model="selectedMeds.{{ $index }}.meds_id"
                                        class="block w-[200rem] rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        <option value="">Select Medication</option>
                                        @foreach ($availableMeds as $m)
                                            <option value="{{ $m->meds_id }}">
                                                {{ $m->meds_name }} (Stock: {{ $m->stock_quantity }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-full md:w-10">
                                    <flux:input label="Amount" type="number" placeholder="Qty"
                                        wire:model="selectedMeds.{{ $index }}.amount" />
                                    <flux:error name="selectedMeds.{{ $index }}.amount" />
                                </div>
                                <div class="w-full md:w-full">
                                    <flux:input label="Dosage" placeholder="e.g. 1 tab 3x"
                                        wire:model="selectedMeds.{{ $index }}.dosage" />
                                </div>
                                <flux:button square variant="danger" icon="trash"  wire:click="removeMedicationRow({{ $index }})" />
                            </div>
                        @endforeach
                    </div>

                    <div class="flex">
                        <flux:button variant="ghost" icon="plus" wire:click="addMedicationRow">Add Medication</flux:button>
                    </div>
                </flux:card>

                    <!-- Medical Certificate -->
                    <flux:card class="p-4 space-y-4">
                        <h3 class="font-semibold text-lg">Medical Certificate (Optional)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:input label="Start Date" type="date" wire:model="mc_start_date" />
                            <flux:input label="Number of Days" type="number" min="0" wire:model="mc_days" />
                        </div>
                    </flux:card>

                    <div class="flex justify-end pt-4">
                        <flux:button variant="primary" type="submit" icon="check">Save Consultation</flux:button>
                    </div>
                </form>
        @endif
    </div>

</div>
