<?php

use Livewire\Volt\Component;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PrescribedMed;
use Carbon\Carbon;

new class extends Component {
    public $reportPeriod = 'today'; // today, week, month

    public function with(): array
    {
        $startDate = match ($this->reportPeriod) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
        };

        // Stats
        $totalPatients = Patient::count();
        $totalAppointments = Appointment::where('created_at', '>=', $startDate)->count();
        $completedAppointments = Appointment::where('created_at', '>=', $startDate)->where('appt_status', 'COMPLETED')->count();

        // Revenue (Mock calculation since payment is 0.00 default)
        // Assuming RM50 per consultation for visualization
        $estimatedRevenue = $completedAppointments * 50;

        // Chart Data: Appointments per day (Last 7 days)
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('D');
            $chartData[] = Appointment::whereDate('appt_date', $date->format('Y-m-d'))->count();
        }

        // Top Meds
        $topMeds = PrescribedMed::select('meds_id', \DB::raw('count(*) as total'))
            ->with('medication')
            ->groupBy('meds_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return [
            'stats' => compact('totalPatients', 'totalAppointments', 'completedAppointments', 'estimatedRevenue'),
            'chart' => compact('chartLabels', 'chartData'),
            'topMeds' => $topMeds,
        ];
    }

    public function downloadReport()
    {
        // CSV Export Logic Placeholder
        \Flux::toast('Report download started...');
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Head Office Analytics</h1>
            <p class="text-sm text-gray-600">Facility performance and operational metrics.</p>
        </div>

        <div class="flex items-center gap-2">
            <flux:select wire:model.live="reportPeriod" class="w-32">
                <flux:select.option value="today">Today</flux:select.option>
                <flux:select.option value="week">This Week</flux:select.option>
                <flux:select.option value="month">This Month</flux:select.option>
            </flux:select>
            <flux:button icon="arrow-down-tray" wire:click="downloadReport">Export</flux:button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <flux:card>
            <div class="text-sm text-gray-600">Total Patients</div>
            <div class="text-2xl font-bold">{{ $stats['totalPatients'] }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-gray-600">Appointments (Period)</div>
            <div class="text-2xl font-bold">{{ $stats['totalAppointments'] }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-gray-600">Completed Visits</div>
            <div class="text-2xl font-bold">{{ $stats['completedAppointments'] }}</div>
        </flux:card>
        <flux:card>
            <div class="text-sm text-gray-600">Est. Revenue</div>
            <div class="text-2xl font-bold text-green-600">RM {{ number_format($stats['estimatedRevenue'], 2) }}</div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Chart Placeholder -->
        <flux:card class="md:col-span-2">
            <h3 class="font-semibold mb-4">Appointments Trend (Last 7 Days)</h3>
            <div class="h-64 flex items-end justify-between gap-2 px-4">
                @foreach($chart['chartData'] as $index => $value)
                    <div class="w-full flex flex-col items-center gap-2">
                        <div class="w-full bg-teal-100 rounded-t-md relative group hover:bg-teal-200 transition-all"
                            style="height: {{ $value ? ($value / (max($chart['chartData']) ?: 1) * 100) : 0 }}%">
                            <div
                                class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                {{ $value }}
                            </div>
                        </div>
                        <div class="text-xs text-gray-600">{{ $chart['chartLabels'][$index] }}</div>
                    </div>
                @endforeach
            </div>
        </flux:card>

        <!-- Top Medications -->
        <flux:card>
            <h3 class="font-semibold mb-4">Top Prescribed Meds</h3>
            <div class="space-y-3">
                @foreach($topMeds as $item)
                    <div class="flex items-center justify-between text-sm">
                        <span>{{ $item->medication->meds_name ?? 'Unknown' }}</span>
                        <flux:badge>{{ $item->total }}</flux:badge>
                    </div>
                @endforeach
                @if($topMeds->isEmpty())
                    <p class="text-gray-600 italic">No prescription data available.</p>
                @endif
            </div>
        </flux:card>
    </div>
</div>