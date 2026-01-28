<?php

use Livewire\Volt\Component;
use App\Models\Appointment;

new class extends Component {
    public function with(): array
    {
        return [
            'queue' => Appointment::with('patient', 'doctor')
                ->where('appt_date', now()->format('Y-m-d'))
                ->whereIn('appt_status', ['CONFIRMED', 'ARRIVED', 'CONSULTING'])
                ->orderBy('appt_time')
                ->get(),
        ];
    }

    public function updateStatus($id, $status)
    {
        $appt = Appointment::find($id);
        if ($appt) {
            $appt->update(['appt_status' => $status]);

            // Log action
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_queue_status',
                'description' => "Updated status to {$status} for Appointment ID: {$id}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            \Flux::toast("Patient marked as {$status}");
        }
    }
};
?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Today's Queue</h1>
        <p class="text-sm text-gray-600">{{ now()->format('l, d F Y') }}</p>
    </div>

    @if($queue->isEmpty())
        <div class="text-center py-12 text-gray-600">
            <p>No active patients in queue.</p>
        </div>
    @else
        <div class="grid gap-4">
            @foreach($queue as $appt)
                <flux:card
                    class="flex items-center justify-between border-l-4 {{ $appt->appt_status === 'CONSULTING' ? 'border-l-green-500' : 'border-l-blue-500' }}">
                    <div class="flex items-center gap-4">
                        <div class="text-center min-w-[3rem]">
                            <div class="text-xs text-gray-600 uppercase font-bold">Time</div>
                            <div class="font-mono font-bold">{{ $appt->appt_time }}</div>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg">{{ $appt->patient->patient_name }}</h4>
                            <p class="text-sm text-gray-600">Dr. {{ $appt->doctor->doctor_name }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($appt->appt_status === 'CONFIRMED')
                            <flux:button size="sm" icon="check" wire:click="updateStatus({{ $appt->appt_ID }}, 'ARRIVED')">Check In
                            </flux:button>
                        @elseif($appt->appt_status === 'ARRIVED')
                            <flux:button size="sm" color="green" icon="play"
                                wire:click="updateStatus({{ $appt->appt_ID }}, 'CONSULTING')">Start Consult</flux:button>
                        @elseif($appt->appt_status === 'CONSULTING')
                            <flux:badge color="green" class="animate-pulse">In Consultation</flux:badge>
                        @endif
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>