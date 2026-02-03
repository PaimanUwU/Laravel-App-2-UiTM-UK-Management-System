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
        $next30Days = now()->addDays(30)->format('Y-m-d');

        $user = auth()->user();
        $doctor = $user->doctor;

        $query = Appointment::with(['patient', 'doctor']);

        if ($doctor) {
            $query->where('doctor_id', $doctor->doctor_id);
        }

        // Appointment status breakdown for pie chart (Oracle-compatible)
        $statusQuery = Appointment::query();
        if ($doctor) {
            $statusQuery->where('doctor_id', $doctor->doctor_id);
        }
        
        $appointmentsByStatus = $statusQuery
            ->selectRaw('appt_status, COUNT(*) as count')
            ->groupBy('appt_status')
            ->get()
            ->pluck('count', 'appt_status')
            ->toArray();

        return [
            'todayAppointments' => (clone $query)
                ->where('appt_date', $today)
                ->orderBy('appt_time')
                ->get(),
            'monthAppointments' => Appointment::with(['patient', 'doctor'])
                ->whereBetween('appt_date', [$today, $next30Days])
                ->whereIn('appt_status', ['PENDING', 'Pending', 'Scheduled', 'scheduled'])
                ->orderBy('appt_date')
                ->orderBy('appt_time')
                ->get(),
            'appointmentsByStatus' => $appointmentsByStatus,
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

    <!-- Appointment Status Overview -->
    @if(!empty($appointmentsByStatus))
        <section class="mb-8">
            <div class="bg-white border border-gray-100 rounded-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Appointment Status Overview</h2>
                        <p class="text-sm text-gray-500 font-medium">Distribution of all appointments by status</p>
                    </div>
                </div>
                <div class="relative h-64 flex items-center justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </section>
    @endif

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

    <!-- Monthly Appointment Requests -->
    <section class="space-y-4 mb-4">
        <div class="flex items-center gap-2">
            <flux:icon.clock class="w-5 h-5 text-orange-600" />
            <h2 class="text-lg font-bold text-gray-800">Appointment Requests</h2>
            <span
                class="text-xs font-medium bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Next 30 Days</span>
        </div>

        @if($monthAppointments->isEmpty())
            <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <p class="text-sm text-gray-500">No pending appointment requests found for the next 30 days.</p>
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

@if(!empty($appointmentsByStatus))
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            function initStatusChart() {
                const ctx = document.getElementById('statusChart');
                
                if (!ctx) {
                    console.error('Status chart canvas not found');
                    return;
                }

                if (typeof Chart === 'undefined') {
                    console.error('Chart.js not loaded');
                    return;
                }

                // Destroy existing chart if it exists
                const existingChart = Chart.getChart(ctx);
                if (existingChart) {
                    existingChart.destroy();
                }

                const statusData = @json($appointmentsByStatus);
                const labels = Object.keys(statusData);
                const data = Object.values(statusData);

                // Define colors for different statuses
                const colorMap = {
                    'PENDING': 'rgba(251, 191, 36, 0.8)',      // Amber
                    'SCHEDULED': 'rgba(59, 130, 246, 0.8)',    // Blue
                    'CONFIRMED': 'rgba(34, 197, 94, 0.8)',     // Green
                    'COMPLETED': 'rgba(16, 185, 129, 0.8)',    // Emerald
                    'CANCELLED': 'rgba(239, 68, 68, 0.8)',     // Red
                    'ARRIVED': 'rgba(249, 115, 22, 0.8)',      // Orange
                    'DISCHARGED': 'rgba(107, 114, 128, 0.8)',  // Gray
                };

                const backgroundColors = labels.map(label => 
                    colorMap[label.toUpperCase()] || 'rgba(156, 163, 175, 0.8)'
                );

                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: backgroundColors,
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: 'rgba(255, 255, 255, 0.1)',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                initStatusChart();
                setTimeout(initStatusChart, 100);
            });

            // Re-initialize on Livewire updates
            document.addEventListener('livewire:navigated', initStatusChart);

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('morph.updated', ({ component }) => {
                    setTimeout(initStatusChart, 100);
                });
            }
        </script>
    @endpush
@endif
