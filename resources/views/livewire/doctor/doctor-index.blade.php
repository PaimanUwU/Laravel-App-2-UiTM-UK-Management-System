<?php

use Livewire\Volt\Component;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

new class extends Component {
  public function with(): array
  {
    $user = auth()->user();
    $isDoctor = $user->hasRole('doctor');

    // Fetch all doctors with their positions, departments, and appointment counts
    // Doctors see "Today's Workload", Admins see "Overall Workload"
    $query = Doctor::with(['position', 'department']);

    if ($isDoctor) {
      $today = now()->format('Y-m-d');
      $query->withCount([
        'appointments as appointments_count' => function ($q) use ($today) {
          $q->where('appt_date', $today);
        }
      ]);
      $workloadTitle = "Today's Workload";
      $workloadDesc = "Comparison of appointments scheduled for today across all doctors.";
      $headerText = "workload for " . now()->format('d M Y');
    } else {
      $query->withCount('appointments');
      $workloadTitle = "Overall Workload";
      $workloadDesc = "Comparison of total appointments scheduled across all doctors.";
      $headerText = "overall workload";
    }

    $doctors = $query->get();

    // Prepare chart data: Doctor Names and their Appointment Counts
    $chartLabels = $doctors->pluck('doctor_name')->toArray();
    $chartData = $doctors->pluck('appointments_count')->toArray();

    // Stats
    $totalDoctors = $doctors->count();
    $activeDoctors = $doctors->where('status', 'ACTIVE')->count();

    return [
      'doctors' => $doctors,
      'chart' => [
        'labels' => $chartLabels,
        'data' => $chartData,
      ],
      'stats' => [
        'total' => $totalDoctors,
        'active' => $activeDoctors,
      ],
      'workloadTitle' => $workloadTitle,
      'workloadDesc' => $workloadDesc,
      'headerText' => $headerText,
    ];
  }
};
?>

<div class="space-y-10">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Doctors Directory</h1>
      <p class="text-sm text-gray-600">View all registered doctors and their <span
          class="font-bold text-teal-600">{{ $headerText }}</span>.</p>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <flux:card class="p-4">
      <div class="flex items-center gap-3">
        <div class="p-3 bg-teal-100 rounded-lg text-teal-700">
          <flux:icon.users class="w-6 h-6" />
        </div>
        <div>
          <div class="text-sm text-gray-600 uppercase tracking-wider font-semibold">Total Doctors</div>
          <div class="text-3xl font-bold">{{ $stats['total'] }}</div>
        </div>
      </div>
    </flux:card>
    <flux:card class="p-4">
      <div class="flex items-center gap-3">
        <div class="p-3 bg-blue-100 rounded-lg text-blue-700">
          <flux:icon.check-circle class="w-6 h-6" />
        </div>
        <div>
          <div class="text-sm text-gray-600 uppercase tracking-wider font-semibold">Active Doctors</div>
          <div class="text-3xl font-bold">{{ $stats['active'] }}</div>
        </div>
      </div>
    </flux:card>
  </div>

  <!-- Workload Graph -->
  <section>
    <flux:card class="p-4">
      <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-900">{{ $workloadTitle }}</h2>
        <p class="text-sm text-gray-500">{{ $workloadDesc }}</p>
      </div>
      <div class="relative h-120">
        <canvas id="workloadChart"></canvas>
      </div>
    </flux:card>
  </section>

  <!-- Doctors Table -->
  <section>
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Doctor</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Position/Dept</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Workload</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @foreach($doctors as $doctor)
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div
                    class="mr-4 flex-shrink-0 h-10 w-10 bg-teal-50 flex items-center justify-center rounded-full text-teal-700 font-bold">
                    {{ strtoupper(substr($doctor->doctor_name, 0, 1)) }}
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ $doctor->doctor_name }}</div>
                    <div class="text-sm text-gray-500">{{ $doctor->doctor_email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ $doctor->position->position_name ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500">{{ $doctor->department->dept_name ?? 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $doctor->status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                  {{ $doctor->status ?? 'UNKNOWN' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-gray-900">{{ $doctor->appointments_count }}</span>
                  <span class="text-xs text-gray-400">appointments</span>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
</div>

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script>
    function initWorkloadChart() {
      const ctx = document.getElementById('workloadChart');
      if (!ctx) return;

      const existingChart = Chart.getChart(ctx);
      if (existingChart) {
        existingChart.destroy();
      }

      const chartData = @json($chart);

      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: chartData.labels,
          datasets: [{
            label: 'Total Appointments',
            data: chartData.data,
            backgroundColor: 'rgba(20, 184, 166, 0.6)', // Teal
            borderColor: 'rgb(20, 184, 166)',
            borderWidth: 1,
            borderRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1,
                font: { size: 12 }
              },
              grid: { borderDash: [5, 5] }
            },
            x: {
              ticks: { font: { size: 12 } },
              grid: { display: false }
            }
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              padding: 12,
              titleFont: { size: 14, weight: 'bold' },
              bodyFont: { size: 13 },
              callbacks: {
                label: function (context) {
                  return ` Workload: ${context.parsed.y} appointments`;
                }
              }
            }
          }
        }
      });
    }

    document.addEventListener('DOMContentLoaded', initWorkloadChart);
    document.addEventListener('livewire:navigated', initWorkloadChart);

    if (typeof Livewire !== 'undefined') {
      Livewire.hook('morph.updated', ({ component }) => {
        setTimeout(initWorkloadChart, 100);
      });
    }
  </script>
@endpush