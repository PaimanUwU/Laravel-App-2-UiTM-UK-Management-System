<?php

use Livewire\Volt\Component;
use App\Models\Appointment;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $filterDate = '';

    public function mount()
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    public function with(): array
    {
        $query = Appointment::with(['patient', 'doctor'])->orderBy('appt_time');

        if ($this->filterDate) {
            $query->where('appt_date', $this->filterDate);
        }

        return [
            'appointments' => $query->get(),
        ];
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Appointment Calendar</h1>
            <p class="text-sm text-gray-600">Manage daily schedules and bookings.</p>
        </div>
        <div class="flex gap-2">
            <flux:button icon="plus" variant="primary" href="{{ route('appointments.create') }}">
                Book Appointment
            </flux:button>
        </div>
    </div>

    <div class="flex gap-4 items-end">
        <flux:input wire:model.live="filterDate" type="date" label="Filter by Date" class="w-48" />
    </div>

    @if($appointments->isEmpty())
        <div class="text-center py-12 bg-white rounded-lg border border-dashed border-gray-300">
            <flux:icon.calendar class="w-12 h-12 mx-auto mb-3 text-gray-300" />
            <h3 class="text-lg font-medium text-gray-900">No appointments found</h3>
            <p class="text-gray-600">There are no bookings for this date.</p>
        </div>
    @else
        <div class="grid gap-4">
            @foreach($appointments as $appt)
                <flux:card class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col items-center justify-center w-16 h-16 bg-teal-50 rounded-lg text-teal-700">
                            <span class="text-lg font-bold">{{ $appt->appt_time }}</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">{{ $appt->patient->patient_name ?? 'Unknown Patient' }}</h4>
                            <p class="text-sm text-gray-600">Dr. {{ $appt->doctor->doctor_name ?? 'Unassigned' }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <flux:badge size="sm" color="{{ match ($appt->appt_status) {
                    'COMPLETED' => 'green',
                    'CANCELLED' => 'red',
                    'CONFIRMED' => 'blue',
                    default => 'zinc',
                } }}">
                                    {{ $appt->appt_status }}
                                </flux:badge>
                                @if($appt->appt_note)
                                    <span
                                        class="text-xs text-gray-600 border-l border-gray-200 pl-2 ml-1">{{ Str::limit($appt->appt_note, 30) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:button variant="ghost" size="sm" icon="pencil-square"
                            href="{{ route('appointments.edit', $appt->appt_ID) }}" />
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>