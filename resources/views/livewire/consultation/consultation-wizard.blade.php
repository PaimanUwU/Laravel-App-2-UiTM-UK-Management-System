<?php

use Livewire\Volt\Component;
use App\Models\Appointment;
use App\Models\MedicalCheckup;
use App\Models\PrescribedMed;
use App\Models\MedicalCertificate;
use App\Models\Medication;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public Appointment $appointment;
    public $step = 'vitals'; // vitals, notes, meds, mc, finish

    // Vitals
    public $vital_bp = '';
    public $vital_heart_rate = '';
    public $vital_weight = '';
    public $vital_height = '';

    // Notes
    public $symptoms = '';
    public $diagnosis = '';
    public $treatment = '';
    public $notes = ''; // Appointment notes

    // Prescription
    public $med_search = '';
    public $prescriptions = []; // Array of ['meds_ID', 'name', 'amount', 'dosage']

    // MC
    public $mc_start_date = '';
    public $mc_days = 0;

    public function mount(Appointment $appointment)
    {
        $this->appointment = $appointment;
        $this->notes = $appointment->appt_note;

        // Load existing data
        $checkup = MedicalCheckup::find($appointment->appt_id);
        if ($checkup) {
            $this->symptoms = $checkup->checkup_symptom;
            $this->diagnosis = $checkup->checkup_finding; // Aligned to checkup_finding
            $this->treatment = $checkup->checkup_treatment;
            $this->vital_bp = $checkup->vital_bp;
            $this->vital_heart_rate = $checkup->vital_heart_rate;
            $this->vital_weight = $checkup->vital_weight;
            $this->vital_height = $checkup->vital_height;
        }

        $this->loadPrescriptions();

        $mc = MedicalCertificate::where('appt_id', $appointment->appt_id)->first();
        if ($mc) {
            $this->mc_start_date = $mc->mc_date_start;
            $this->mc_days = \Carbon\Carbon::parse($mc->mc_date_start)->diffInDays($mc->mc_date_end) + 1;
        }
    }

    public function loadPrescriptions()
    {
        $this->prescriptions = PrescribedMed::with('medication')
            ->where('appt_id', $this->appointment->appt_id)
            ->get()
            ->map(fn($p) => [
                'id' => $p->prescribe_id,
                'meds_id' => $p->meds_id,
                'name' => $p->medication->meds_name ?? 'Unknown',
                'amount' => $p->amount,
                'dosage' => $p->dosage,
            ])
            ->toArray();
    }

    public function saveVitals()
    {
        MedicalCheckup::updateOrCreate(
            ['appt_id' => $this->appointment->appt_id],
            [
                'checkup_symptom' => $this->symptoms,
                'checkup_finding' => $this->diagnosis, // Saved to finding
                'checkup_treatment' => $this->treatment,
                'vital_bp' => $this->vital_bp,
                'vital_heart_rate' => $this->vital_heart_rate,
                'vital_weight' => $this->vital_weight,
                'vital_height' => $this->vital_height,
            ]
        );

        $this->appointment->update(['appt_note' => $this->notes]);

        $this->step = 'meds';
    }

    public function addMedication($meds_ID, $amount, $dosage)
    {
        $med = Medication::find($meds_ID);

        if ($med->stock_quantity < $amount) {
            $this->addError('med_stock', "Insufficient stock for {$med->meds_name}. Available: {$med->stock_quantity}");
            return;
        }

        DB::transaction(function () use ($med, $amount, $dosage) {
            // Deduct stock
            $med->decrement('stock_quantity', $amount);

            // Log movement
            StockMovement::create([
                'meds_id' => $med->meds_id,
                'quantity' => $amount,
                'type' => 'OUT',
                'reason' => 'Prescription for Appt #' . $this->appointment->appt_id,
                'user_id' => auth()->id(),
            ]);

            // Create prescription record
            PrescribedMed::create([
                'appt_id' => $this->appointment->appt_id,
                'meds_id' => $med->meds_id,
                'amount' => $amount,
                'dosage' => $dosage,
            ]);
        });

        $this->loadPrescriptions();
        $this->reset(['med_search']);
        \Flux::toast('Medication added.');
    }

    public function removeMedication($prescribe_ID)
    {
        $pres = PrescribedMed::find($prescribe_ID);
        if ($pres) {
            DB::transaction(function () use ($pres) {
                // Return stock
                $med = Medication::find($pres->meds_id);
                $med->increment('stock_quantity', $pres->amount);

                // Log movement
                StockMovement::create([
                    'meds_id' => $pres->meds_id,
                    'quantity' => $pres->amount,
                    'type' => 'IN',
                    'reason' => 'Prescription Cancelled Appt #' . $this->appointment->appt_id,
                    'user_id' => auth()->id(),
                ]);

                $pres->delete();
            });
            $this->loadPrescriptions();
            \Flux::toast('Medication removed.');
        }
    }

    public function saveMC()
    {
        if ($this->mc_start_date && $this->mc_days > 0) {
            MedicalCertificate::updateOrCreate(
                ['appt_id' => $this->appointment->appt_id],
                [
                    'mc_date_start' => $this->mc_start_date,
                    'mc_date_end' => \Carbon\Carbon::parse($this->mc_start_date)->addDays($this->mc_days - 1),
                ]
            );
        }
        $this->step = 'finish';
    }

    public function completeConsultation()
    {
        $this->appointment->update(['appt_status' => 'COMPLETED']);

        return redirect()->route('dashboard')->with('status', 'Consultation completed.');
    }

    public function with()
    {
        return [
            'medSearch' => $this->med_search
                ? Medication::where('meds_name', 'like', '%' . $this->med_search . '%')->limit(5)->get()
                : [],
        ];
    }
};
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Consultation Room</h1>
            <p class="text-sm text-gray-600">
                Patient: <span class="font-semibold">{{ $appointment->patient->patient_name }}</span> |
                Ticket: #{{ $appointment->appt_id }}
            </p>
        </div>
        <div class="flex gap-2">
            <flux:badge size="lg" color="green">
                {{ strtoupper($step) }}
            </flux:badge>
        </div>
    </div>

    {{-- Step Navigation --}}
    <div class="flex border-b border-gray-200">
        <button wire:click="$set('step', 'vitals')"
            class="px-4 py-2 {{ $step === 'vitals' ? 'border-b-2 border-teal-500 font-bold' : '' }}">1. Clinical
            Notes</button>
        <button wire:click="$set('step', 'meds')"
            class="px-4 py-2 {{ $step === 'meds' ? 'border-b-2 border-teal-500 font-bold' : '' }}">2.
            Prescription</button>
        <button wire:click="$set('step', 'mc')"
            class="px-4 py-2 {{ $step === 'mc' ? 'border-b-2 border-teal-500 font-bold' : '' }}">3. Medical
            Cert</button>
        <button wire:click="$set('step', 'finish')"
            class="px-4 py-2 {{ $step === 'finish' ? 'border-b-2 border-teal-500 font-bold' : '' }}">4. Finish</button>
    </div>

    {{-- Step 1: Notes --}}
    @if($step === 'vitals')
        <flux:card class="space-y-4">
            <!-- Vitals Section -->
            <div class="p-4 bg-gray-50 rounded-lg space-y-2">
                <h3 class="font-semibold text-sm text-gray-700">Vitals</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <flux:input label="Blood Pressure" placeholder="120/80" wire:model="vital_bp" />
                    <flux:input label="Heart Rate (bpm)" type="number" wire:model="vital_heart_rate" />
                    <flux:input label="Weight (kg)" type="number" step="0.1" wire:model="vital_weight" />
                    <flux:input label="Height (cm)" type="number" step="0.1" wire:model="vital_height" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <flux:textarea wire:model="symptoms" label="Symptoms / Complaints" value="{{ $symptoms }}"
                    placeholder="Patient complains of..." />
                <flux:textarea wire:model="diagnosis" label="Diagnosis / Findings" value="{{ $diagnosis }}"
                    placeholder="Viral fever..." />
                <flux:textarea wire:model="treatment" label="Treatment Plan" value="{{ $treatment }}"
                    placeholder="Rest and fluids..." />
                <flux:textarea wire:model="notes" label="General Notes" value="{{ $notes }}"
                    placeholder="Additional notes..." />
            </div>
            <div class="flex justify-end">
                <flux:button variant="primary" wire:click="saveVitals">Next: Prescribe</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- Step 2: Meds --}}
    @if($step === 'meds')
        <flux:card class="space-y-6">
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="font-bold mb-2">Add Medication</h3>
                <div class="flex gap-2" x-data="{ amount: 1, dosage: '1 tab 3x daily' }">
                    <div class="flex-1 relative">
                        <flux:input wire:model.live="med_search" placeholder="Search drug..." />
                        @if(count($medSearch) > 0)
                            <div class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1">
                                @foreach($medSearch as $m)
                                    <div class="p-2 hover:bg-gray-100 cursor-pointer flex justify-between"
                                        wire:click="addMedication({{ $m->meds_id }}, amount, dosage)">
                                        <span>{{ $m->meds_name }}</span>
                                        <span class="text-xs text-gray-600">Stock: {{ $m->stock_quantity }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <flux:input x-model="amount" type="number" class="w-24" placeholder="Qty" />
                    <flux:input x-model="dosage" class="w-48" placeholder="Dosage" />
                </div>
                <flux:error name="med_stock" />
            </div>

            <div class="space-y-2">
                <h3 class="font-bold">Prescribed Items</h3>
                @foreach($prescriptions as $p)
                    <div class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg shadow-sm">
                        <div>
                            <span class="font-semibold">{{ $p['name'] }}</span>
                            <span class="text-sm text-gray-600">x {{ $p['amount'] }} ({{ $p['dosage'] }})</span>
                        </div>
                        <flux:button variant="ghost" size="sm" icon="trash" wire:click="removeMedication({{ $p['id'] }})" />
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between mt-4">
                <flux:button wire:click="$set('step', 'vitals')">Back</flux:button>
                <flux:button variant="primary" wire:click="$set('step', 'mc')">Next: Medical Cert</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- Step 3: MC --}}
    @if($step === 'mc')
        <flux:card class="space-y-4">
            <h3 class="font-bold">Generate Medical Certificate</h3>
            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="mc_start_date" type="date" label="Start Date" />
                <flux:input wire:model="mc_days" type="number" label="Number of Days" />
            </div>
            <div class="flex justify-between mt-4">
                <flux:button wire:click="$set('step', 'meds')">Back</flux:button>
                <flux:button variant="primary" wire:click="saveMC">Next: Review & Finish</flux:button>
            </div>
        </flux:card>
    @endif

    {{-- Step 4: Finish --}}
    @if($step === 'finish')
        <flux:card class="text-center py-12 space-y-4">
            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto">
                <flux:icon.check class="w-8 h-8" />
            </div>
            <h2 class="text-xl font-bold">Consultation Summary</h2>
            <div class="text-left max-w-md mx-auto space-y-2 text-sm text-gray-600 bg-gray-50 p-4 rounded-lg">
                <p><strong>Diagnosis:</strong> {{ $diagnosis ?: 'N/A' }}</p>
                <p><strong>Prescriptions:</strong> {{ count($prescriptions) }} items</p>
                <p><strong>MC:</strong> {{ $mc_days > 0 ? "$mc_days Days" : 'None' }}</p>
            </div>
            <div class="pt-4">
                <flux:button variant="primary" wire:click="completeConsultation" class="w-full md:w-auto">
                    Complete & Close Session
                </flux:button>
            </div>
        </flux:card>
    @endif
</div>