<?php

use Livewire\Volt\Component;
use App\Models\Appointment;
use App\Models\Doctor;

new class extends Component {
    public $doctor;
    public $activeTab = 'queue';

    public function mount()
    {
        $this->doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$this->doctor) {
            abort(403, 'User is not linked to a Doctor profile.');
        }
    }

    public function toggleStatus()
    {
        $newStatus = $this->doctor->status === 'ACTIVE' ? 'BREAK' : 'ACTIVE';
        $this->doctor->update(['status' => $newStatus]);

        flux()->toast("Status updated to {$newStatus}");
    }

    public function with(): array
    {
        return [
            'queue' => Appointment::with('patient')
                ->where('doctor_id', $this->doctor->doctor_id)
                ->where('appt_date', now()->format('Y-m-d'))
                ->whereIn('appt_status', ['CONFIRMED', 'ARRIVED', 'CONSULTING'])
                ->orderBy('appt_time')
                ->get(),
        ];
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dr. {{ $doctor->doctor_name }}</h1>
            <p class="text-sm text-gray-600">{{ $doctor->position->position_name ?? 'Doctor' }} •
                {{ $doctor->department->dept_name ?? 'General' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-600">Workload Status:</span>
            <flux:switch wire:click="toggleStatus" :checked="$doctor->status === 'ACTIVE'" />
            <flux:badge color="{{ $doctor->status === 'ACTIVE' ? 'green' : 'orange' }}">
                {{ $doctor->status }}
            </flux:badge>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Stats -->
        <flux:card class="md:col-span-1 space-y-4">
            <div class="flex items-center gap-2">
                <flux:icon.user-group variant="mini" class="text-blue-500" />
                <h3 class="font-semibold">Today's Load</h3>
            </div>
            <div class="text-3xl font-bold">{{ $queue->count() }}</div>
            <p class="text-sm text-gray-600">Patients remaining</p>
        </flux:card>

        <!-- Queue List -->
        <flux:card class="md:col-span-2 space-y-4">
            <h3 class="font-semibold">My Queue</h3>
            @if($queue->isEmpty())
                <p class="text-gray-600 italic">No active patients assigned to you.</p>
            @else
                <div class="space-y-3">
                    @foreach($queue as $appt)
                        <div
                            class="flex items-center justify-between p-3 border rounded-lg {{ $appt->appt_status === 'CONSULTING' ? 'border-green-400 bg-green-50' : 'border-gray-100' }}">
                            <div class="flex items-center gap-3">
                                <div class="font-mono font-bold text-gray-600">{{ $appt->appt_time }}</div>
                                <div>
                                    <div class="font-semibold">{{ $appt->patient->patient_name }}</div>
                                    <div class="text-xs text-gray-600">Ticket #{{ $appt->appt_id }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($appt->appt_status === 'CONSULTING')
                                    <flux:button size="sm" variant="primary"
                                        href="{{ route('consultation.wizard', $appt->appt_id) }}">Continue</flux:button>
                                @else
                                    <flux:button size="sm" href="{{ route('consultation.wizard', $appt->appt_id) }}">Start
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </flux:card>
    </div>
</div>