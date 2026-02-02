<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new class extends Component {
  public $patient;
  public $appointmentForm = [
    'reason' => '',
    'preferred_date' => '',
    'preferred_time' => '',
  ];

  public function mount()
  {
    $user = Auth::user();
    // Assuming strict one-to-one or valid relation exists. 
    // If not, we might need to handle the "profile not created" case.
    $this->patient = $user->patient;
  }

  public function requestAppointment()
  {
    $this->validate([
      'appointmentForm.reason' => 'required|string|max:255',
      'appointmentForm.preferred_date' => 'required|date|after:today',
      // Simple time validation, can be more robust
      'appointmentForm.preferred_time' => 'required',
    ]);

    if (!$this->patient) {
      \Flux::toast('Patient profile not found. Please contact admin.', 'warning');
      return;
    }

    Appointment::create([
      'patient_id' => $this->patient->patient_id,
      'doctor_id' => null, // Assigned by admin/staff later
      'appt_date' => $this->appointmentForm['preferred_date'],
      'appt_time' => $this->appointmentForm['preferred_time'], // Format this correctly if needed
      'appt_status' => 'Scheduled', // Or 'Pending' if that's the flow
      'appt_note' => $this->appointmentForm['reason'],
      'appt_payment' => 0,
    ]);

    $this->reset('appointmentForm');
    \Flux::toast('Appointment requested successfully.');
  }

  public function with(): array
  {
    if (!$this->patient) {
      return [
        'upcoming' => null,
        'history' => collect(),
      ];
    }

    return [
      'upcoming' => Appointment::where('patient_id', $this->patient->patient_id)
        ->where('appt_status', 'Scheduled') // Adjust status based on DB values
        ->whereDate('appt_date', '>=', now())
        ->orderBy('appt_date')
        ->first(),
      'history' => Appointment::with(['doctor', 'medicalCheckup'])
        ->where('patient_id', $this->patient->patient_id)
        ->whereIn('appt_status', ['Completed', 'completed'])
        ->latest('appt_date')
        ->take(5)
        ->get(),
    ];
  }
};
?>

<div class="space-y-6">
  @if(!$patient)
    <flux:card class="text-center p-8">
      <h2 class="text-xl font-bold">Profile Not Found</h2>
      <p class="text-gray-600">Please contact the administration to set up your patient usage profile.</p>
    </flux:card>
  @else
    <!-- Welcome Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Welcome, {{ explode(' ', $patient->patient_name)[0] }}</h1>
        <p class="text-gray-600">Manage your health and appointments.</p>
      </div>
      <flux:badge size="lg" color="blue">{{ $patient->patient_type }}</flux:badge>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Status & Request -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Upcoming Appointment -->
        <flux:card>
          <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
              <flux:icon.calendar variant="solid" class="w-6 h-6" />
            </div>
            <h3 class="font-bold text-lg">Current Status</h3>
          </div>

          @if($upcoming)
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-bold text-blue-800 uppercase tracking-wide mb-1">Upcoming Appointment</p>
                  <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black text-blue-900">
                      {{ \Carbon\Carbon::parse($upcoming->appt_date)->format('d M') }}
                    </span>
                    <span class="text-lg font-medium text-blue-700">
                      {{ \Carbon\Carbon::parse($upcoming->appt_time)->format('h:i A') }}
                    </span>
                  </div>
                  <p class="text-blue-700 mt-2">
                    <span class="font-semibold">Doctor:</span>
                    {{ $upcoming->doctor->doctor_name ?? 'To be assigned' }}
                  </p>
                </div>
                <flux:badge color="blue" variant="solid" class="uppercase text-xs font-bold tracking-widest">
                  {{ $upcoming->appt_status }}
                </flux:badge>
              </div>
            </div>
          @else
            <div class="bg-gray-50 border border-gray-100 rounded-xl p-5 text-center">
              <p class="text-gray-500 font-medium">No active appointments.</p>
              <p class="text-xs text-gray-400 mt-1">Request one below if you're not feeling well.</p>
            </div>
          @endif
        </flux:card>

        <!-- Medical History -->
        <flux:card>
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg">Medical History</h3>
            <flux:button variant="ghost" size="sm" icon="arrow-right"
              href="{{ route('patients.show', $patient->patient_id) }}">View All</flux:button>
          </div>

          <div class="space-y-3">
            @forelse($history as $record)
              <a href="{{ route('patients.show', $patient->patient_id) }}?tab=history"
                class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors block cursor-pointer">
                <div class="flex items-center gap-4">
                  <div class="text-center min-w-[3rem]">
                    <div class="text-xs font-bold text-gray-400 uppercase">
                      {{ \Carbon\Carbon::parse($record->appt_date)->format('M') }}
                    </div>
                    <div class="text-xl font-black text-gray-800 leading-none">
                      {{ \Carbon\Carbon::parse($record->appt_date)->format('d') }}
                    </div>
                  </div>
                  <div>
                    <p class="font-bold text-gray-900">
                      {{ $record->medicalCheckup->checkup_finding ?? 'Checkup' }}
                    </p>
                    <p class="text-sm text-gray-500">
                      Dr. {{ $record->doctor->doctor_name ?? 'Unknown' }}
                    </p>
                  </div>
                </div>
                <flux:badge size="sm" color="zinc">Completed</flux:badge>
              </a>
            @empty
              <div class="text-center py-8 text-gray-400">
                <flux:icon.clipboard-document-list class="w-10 h-10 mx-auto mb-2 opacity-30" />
                <p>No medical history found.</p>
              </div>
            @endforelse
          </div>
        </flux:card>
      </div>

      <!-- Right Column: Request Form -->
      <div class="lg:col-span-1">
        <flux:card class="h-full flex flex-col">
          <div class="flex items-center gap-2 mb-6">
            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
              <flux:icon.plus variant="solid" class="w-5 h-5" />
            </div>
            <h3 class="font-bold text-lg">Book Appointment</h3>
          </div>

          <form wire:submit.prevent="requestAppointment" class="space-y-6 flex-1 flex flex-col">
            <flux:textarea label="Reason for Visit" placeholder="Describe your symptoms..."
              wire:model="appointmentForm.reason" rows="4" />

            <div class="grid grid-cols-1 gap-4">
              <flux:input type="date" label="Preferred Date" wire:model="appointmentForm.preferred_date"
                min="{{ date('Y-m-d') }}" />
              <flux:input type="time" label="Preferred Time" wire:model="appointmentForm.preferred_time" />
            </div>

            <div class="mt-auto pt-4">
              <div class="p-3 bg-yellow-50 text-yellow-800 text-xs rounded-lg mb-4">
                <strong>Note:</strong> This is a request. Confirmation depends on doctor availability.
              </div>
              <flux:button type="submit" variant="primary" class="w-full justify-center">
                Submit Request
              </flux:button>
            </div>
          </form>
        </flux:card>
      </div>
    </div>
  @endif
</div>