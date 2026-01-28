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

            session()->flash('message', "Patient marked as {$status}");
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
                <div
                    class="bg-white px-8 py-6 rounded-lg shadow-sm flex items-center justify-between border-l-4 {{ $appt->appt_status === 'CONSULTING' ? 'border-l-green-500' : 'border-l-blue-500' }}">
                    <div class="flex items-center gap-4">
                        <div class="text-center min-w-[3rem]">
                            <div class="text-xs text-gray-600 uppercase font-bold">Time</div>
                            <div class="font-mono font-bold text-gray-900">{{ $appt->appt_time }}</div>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg text-gray-900">{{ $appt->patient->patient_name }}</h4>
                            <p class="text-sm text-gray-600">Dr. {{ $appt->doctor->doctor_name }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($appt->appt_status === 'CONFIRMED')
                            <flux:button size="sm" icon="check" wire:click="updateStatus({{ $appt->appt_id }}, 'ARRIVED')">Check In
                            </flux:button>
                        @elseif($appt->appt_status === 'ARRIVED')
                            <flux:button size="sm" color="green" icon="play"
                                wire:click="updateStatus({{ $appt->appt_id }}, 'CONSULTING')">Start Consult</flux:button>
                        @elseif($appt->appt_status === 'CONSULTING')
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 animate-pulse">
                                In Consultation
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>