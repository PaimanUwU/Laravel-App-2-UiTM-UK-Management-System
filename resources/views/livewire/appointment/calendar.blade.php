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
        $today = now()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');

        return [
            'todayAppointments' => Appointment::with(['patient', 'doctor'])
                ->where('appt_date', $today)
                ->orderBy('appt_time')
                ->get(),
            'monthAppointments' => Appointment::with(['patient', 'doctor'])
                ->whereBetween('appt_date', [$startOfMonth, $endOfMonth])
                ->where('appt_date', '!=', $today)
                ->orderBy('appt_date')
                ->orderBy('appt_time')
                ->get(),
        ];
    }
};
?>

<div class="space-y-10">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Appointment Management</h1>
            <p class="text-sm text-gray-600">Overview of today's schedule and monthly bookings.</p>
        </div>
        <div class="flex gap-2">
            <flux:button icon="plus" variant="primary" href="{{ route('appointments.create') }}">
                Book Appointment
            </flux:button>
        </div>
    </div>

    <!-- Today's Appointments -->
    <section class="space-y-4 mb-4">
        <div class="flex items-center gap-2">
            <flux:icon.calendar-days class="w-5 h-5 text-teal-600" />
            <h2 class="text-lg font-bold text-gray-800">Today's Appointments</h2>
            <span
                class="text-xs font-medium bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full">{{ now()->format('d M Y') }}</span>
        </div>

        @if($todayAppointments->isEmpty())
            <div class="text-center px-4 py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <p class="text-sm text-gray-500">No appointments scheduled for today.</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach($todayAppointments as $appt)
                        <div class="bg-white flex items-center justify-between px-8 py-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="px-4 flex items-center gap-4">
                                <div
                                    class="flex flex-col items-center justify-center w-16 h-16 bg-teal-50 rounded-lg text-teal-700 border border-teal-100 flex-shrink-0">
                                    <span class="text-sm font-bold">{{ $appt->appt_time }}</span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg">
                                        {{ $appt->patient->patient_name ?? 'Unknown Patient' }}
                                    </h4>
                                    <p class="text-sm text-gray-600">Dr. {{ $appt->doctor->doctor_name ?? 'Unassigned' }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        @php
                                            $colorClasses = match (strtoupper($appt->appt_status)) {
                                                'COMPLETED' => 'bg-green-100 text-green-800',
                                                'CANCELLED' => 'bg-red-100 text-red-800',
                                                'CONFIRMED' => 'bg-blue-100 text-blue-800',
                                                'ARRIVED' => 'bg-orange-100 text-orange-800',
                                                default => 'bg-zinc-100 text-zinc-800',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                                            {{ $appt->appt_status }}
                                        </span>
                                        @if($appt->appt_note)
                                            <span class="text-xs text-gray-400">• {{ Str::limit($appt->appt_note, 40) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:button variant="ghost" size="sm" icon="pencil-square"
                                    href="{{ route('appointments.edit', $appt->appt_id) }}" />
                            </div>
                        </div>
                @endforeach
            </div>
        @endif
    </section>

    <hr class="border-gray-100" />

    <!-- Monthly Appointments -->
    <section class="space-y-4 mb-4">
        <div class="flex items-center gap-2">
            <flux:icon.calendar class="w-5 h-5 text-blue-600" />
            <h2 class="text-lg font-bold text-gray-800">Monthly Schedule</h2>
            <span
                class="text-xs font-medium bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ now()->format('F Y') }}</span>
        </div>

        @if($monthAppointments->isEmpty())
            <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <p class="text-sm text-gray-500">No other appointments found for {{ now()->format('F') }}.</p>
            </div>
        @else
            <div class="px-4 overflow-hidden bg-white border border-gray-100 rounded-xl shadow-sm">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr class="py-4">
                            <th scope="col" class="py-4 pl-12 pr-3 text-left text-sm font-semibold text-gray-900">Date</th>
                            <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Time</th>
                            <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Patient</th>
                            <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Doctor</th>
                            <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th scope="col" class="relative py-4 pl-3 pr-12">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($monthAppointments as $appt)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-12 pr-3 text-sm font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($appt->appt_date)->format('d M') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $appt->appt_time }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ $appt->patient->patient_name ?? 'Unknown' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">Dr.
                                    {{ $appt->doctor->doctor_name ?? 'Unassigned' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    @php
                                        $colorClasses = match (strtoupper($appt->appt_status)) {
                                            'COMPLETED' => 'bg-green-100 text-green-800',
                                            'CANCELLED' => 'bg-red-100 text-red-800',
                                            'CONFIRMED' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-zinc-100 text-zinc-800',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                                        {{ $appt->appt_status }}
                                    </span>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-12 text-right text-sm font-medium">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square"
                                        href="{{ route('appointments.edit', $appt->appt_id) }}" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
