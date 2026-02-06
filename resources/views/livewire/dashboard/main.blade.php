<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;

new class extends Component {
  // Helper functions
  private function getCurrentDoctor()
  {
    $user = auth()->user();

    if (!$user) {
      return null;
    }

    // If user is admin, return null (no doctor profile)
    if ($user->hasRole('system_admin')) {
      return null;
    }

    // Try to get the doctor profile
    return $user->doctor;
  }

  private function hasDoctorProfile()
  {
    return $this->getCurrentDoctor() !== null;
  }

  private function calculateTrendData($user)
  {
    $endDate = Carbon::today();
    $startDate = $endDate->copy()->subDays(29); // 30 days total including today

    $dates = [];
    $appointmentsData = [];
    $patientsData = [];

    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
      $dateStr = $date->format('Y-m-d');
      $dates[] = $date->format('M j');

      // Calculate appointments based on user role
      if ($user->hasRole('system_admin')) {
        $appointmentsCount = Appointment::whereDate('appt_date', $dateStr)->count();
      } elseif ($user->hasRole('doctor') && $user->doctor) {
        $appointmentsCount = Appointment::where('doctor_id', $user->doctor->doctor_id)
          ->whereDate('appt_date', $dateStr)->count();
      } else {
        $appointmentsCount = 0;
      }

      // Calculate unique patients based on user role - Fixed for Oracle compatibility
      if ($user->hasRole('system_admin')) {
        $patientsCount = Appointment::whereDate('appt_date', $dateStr)
          ->selectRaw('COUNT(DISTINCT patient_id) as count')
          ->value('count') ?? 0;
      } elseif ($user->hasRole('doctor') && $user->doctor) {
        $patientsCount = Appointment::where('doctor_id', $user->doctor->doctor_id)
          ->whereDate('appt_date', $dateStr)
          ->selectRaw('COUNT(DISTINCT patient_id) as count')
          ->value('count') ?? 0;
      } else {
        $patientsCount = 0;
      }

      $appointmentsData[] = $appointmentsCount;
      $patientsData[] = $patientsCount;
    }

    return [
      'dates' => $dates,
      'appointments' => $appointmentsData,
      'patients' => $patientsData,
    ];
  }


  public function confirmAppointment(int $apptId = null)
  {
    if ($apptId === null) {
      session()->flash('error', 'Invalid appointment ID');
      return;
    }

    // Check if user has doctor profile
    if (!$this->hasDoctorProfile()) {
      session()->flash('error', 'Only doctors can confirm appointments.');
      return;
    }

    $doctor = $this->getCurrentDoctor();
    $appt = Appointment::find($apptId);

    // Allow if assigned to me OR if unassigned (I am claiming it)
    if ($appt && ($appt->doctor_id == $doctor->doctor_id || is_null($appt->doctor_id))) {
      $appt->update([
        'appt_status' => 'CONFIRMED',
        'doctor_id' => $doctor->doctor_id // Assign to me
      ]);
      $this->dispatch('appointment-confirmed'); // Optional: for notifications
    }
  }

  public function with()
  {
    $user = auth()->user();

    // Check for Patient (if not doctor/admin/staff)
    // If user has NO special roles, treat as Patient
    $isPatient = !$user->hasAnyRole(['system_admin', 'doctor', 'staff', 'head_office']);

    if ($isPatient) {
      // Check if patient profile exists, otherwise maybe show incomplete state?
      // For now, let the component handle it or fail, but ensuring routing is correct.
      return [
        'isPatient' => true,
        'isAdmin' => false,
        'isDoctor' => false,
        'supervisee' => null,
      ];
    }

    // Calculate 30-day trend data
    $trendData = $this->calculateTrendData($user);

    $isDoctor = $user->hasRole('doctor');
    $isAdmin = $user->hasRole('system_admin');
    $doctor = $isDoctor ? $user->doctor : null;

    if ($isDoctor && $doctor) {
      $todayAppointments = Appointment::with(['patient', 'doctor'])
        ->where('doctor_id', $doctor->doctor_id)
        ->whereDate('appt_date', Carbon::today())
        ->orderBy('appt_time')
        ->get();

      // Incoming Requests (Pending)
      // Show if: Doctor ID matches OR Doctor ID is NULL (Pool)
      $incomingRequests = Appointment::with(['patient', 'doctor'])
        ->where(function ($query) use ($doctor) {
          $query->where('doctor_id', $doctor->doctor_id)
            ->orWhereNull('doctor_id');
        })
        ->whereIn('appt_status', ['PENDING', 'Pending', 'scheduled', 'Scheduled'])
        ->orderBy('appt_date')
        ->orderBy('appt_time')
        ->get();

      $totalPatients = Appointment::where('doctor_id', $doctor->doctor_id)
        ->selectRaw('COUNT(DISTINCT patient_id) as count')
        ->value('count') ?? 0;

      // Changed to single supervisee
      $supervisee = $doctor->supervisees()->with(['user', 'department', 'position'])->first();

      // Supervisor: Recent Team Activity (Single Supervisee)
      $recentConsultations = collect();
      if ($supervisee) {
        $recentConsultations = Appointment::with(['doctor', 'patient'])
          ->where('doctor_id', $supervisee->doctor_id)
          ->whereIn('appt_status', ['Completed', 'Consulting', 'completed', 'consulting'])
          ->latest('updated_at')
          ->take(5)
          ->get();
      }

      return [
        'isPatient' => false,
        'isDoctor' => true,
        'isAdmin' => $isAdmin,
        'doctor' => $doctor,
        'totalPatients' => $totalPatients,
        'todayAppointmentsCount' => $todayAppointments->count(),
        'pendingAppointmentsCount' => $incomingRequests->count(),
        'todayAppointments' => $todayAppointments,
        'upcomingAppointments' => $incomingRequests, // Replacing Upcoming Arrivals with Incoming Requests as per user intent
        'supervisee' => $supervisee, // Passed as single object or null
        'recentTeamActivity' => $recentConsultations,
        'trendData' => $trendData,
      ];
    }

    // Default/Admin View
    return [
      'isPatient' => false,
      'isDoctor' => false,
      'isAdmin' => $isAdmin,
      'totalPatients' => Patient::count(),
      'todayAppointmentsCount' => Appointment::whereDate('appt_date', Carbon::today())->count(),
      'pendingAppointmentsCount' => Appointment::where('appt_status', 'scheduled')->count(),
      'todayAppointments' => Appointment::with(['patient', 'doctor'])
        ->whereDate('appt_date', Carbon::today())
        ->orderBy('appt_time')
        ->get(),
      'upcomingAppointments' => Appointment::with(['patient', 'doctor'])
        ->whereDate('appt_date', '>', Carbon::today())
        ->orderBy('appt_date')
        ->orderBy('appt_time')
        ->take(5)
        ->get(),
      'supervisee' => null,
      'recentTeamActivity' => collect(),
      'trendData' => $trendData,
    ];
  }
}; ?>

<div>
  @if($isPatient)
    <livewire:patient.patient-dashboard />
  @else
    <div class="space-y-6">
        {{-- <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
          <strong class="font-bold">DEBUG MODE:</strong>
          <span class="block sm:inline">Role: Doctor | Supervisee Found:
            {{ $supervisee ? 'YES (' . $supervisee->doctor_name . ')' : 'NO' }}</span>
        </div> --}}

        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">My Dashboard</h1>
            @if($isAdmin)
            <p class="text-gray-500 font-medium">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}</p>
            @elseif($isDoctor)
            <p class="text-gray-500 font-medium">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}</p>
            @endif
            </div>
          <div class="flex items-center gap-2">

          @if($isAdmin)
            <flux:badge color="blue" variant="solid" class="uppercase tracking-widest font-bold">Admin Account</flux:badge>
            @elseif($isDoctor)
            <flux:badge color="blue" variant="solid" class="uppercase tracking-widest font-bold">Doctor Account</flux:badge>
            @endif
          </div>
        </div>

      <!-- MAIN DASHBOARD CONTENT (Bento Grid) -->
      <div class="flex flex-col w-full gap-4">
        <!-- (Existing Bento Grid content remains similar, just ensuring correct variables are used) -->
        <div class="flex flex-col w-full gap-4">
            @if($isDoctor || $isAdmin)
            <!-- 30-Day Trend Chart -->
            <div class="w-full mt-4">
              <div class="bg-white border border-gray-100 rounded-xl p-6">
                <div class="flex items-center justify-between mb-6">
                  <div>
                    <h3 class="text-lg font-bold text-gray-900">30-Day Activity Trend</h3>
                    <p class="text-sm text-gray-500 font-medium">
                      {{ $isDoctor ? 'Your' : 'System-wide' }} appointment and patient activity
                    </p>
                  </div>
                  <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                      <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                      <span class="text-xs font-medium text-gray-600">Appointments</span>
                    </div>
                  </div>
                </div>
                <div class="relative h-64">
                  <canvas id="trendChart"></canvas>
                </div>
              </div>
            </div>
            @endif

          <div class="flex flex-row w-full gap-4">
            <!-- Total Patients -->
            <div
              class="w-full md:col-span-3 lg:col-span-3 md:row-span-2 bg-white border border-gray-100 rounded-xl p-5 flex flex-col justify-between">
              <div class="flex items-start justify-between">
                <div class="p-3 bg-blue-50 rounded-lg text-blue-700">
                  <flux:icon.users variant="solid" class="w-6 h-6" />
                </div>
              </div>
              <div class="p-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
                  {{ $isDoctor ? 'My Patients' : 'Total Patients' }}
                </p>
                <p class="text-4xl font-black text-gray-900">{{ $totalPatients }}</p>
              </div>
            </div>

            <!-- Today's Appointments -->
            <div
              class="w-full md:col-span-3 lg:col-span-2 md:row-span-2 bg-white border border-gray-100 rounded-xl p-5 flex flex-col justify-between">
              <div class="flex items-start justify-between">
                <div class="p-3 bg-emerald-50 rounded-lg text-emerald-700">
                  <flux:icon.calendar variant="solid" class="w-6 h-6" />
                </div>
              </div>
              <div class="p-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
                  {{ $isDoctor ? 'My Appts' : 'Today' }}
                </p>
                <p class="text-4xl font-black text-gray-900 mb-1">{{ $todayAppointmentsCount }}</p>
              </div>
            </div>

            <!-- Pending Appointments -->
            <div
              class="w-full md:col-span-3 lg:col-span-2 md:row-span-2 bg-white border border-gray-100 rounded-xl p-5 flex flex-col justify-between">
              <div class="flex items-start justify-between">
                <div class="p-4 bg-orange-50 rounded-lg text-orange-700">
                  <flux:icon.clock variant="solid" class="w-6 h-6" />
                </div>
              </div>
              <div class="p-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pending</p>
                <p class="text-4xl font-black text-gray-900 mb-1">{{ $pendingAppointmentsCount }}</p>
              </div>
            </div>
          </div>

          <div class="flex flex-row w-full gap-5 mt-4">
            <!-- Quick Actions -->
            <div
              class="w-full md:col-span-6 lg:col-span-4 md:row-span-2 text-white rounded-xl overflow-hidden relative group border border-gray-100">
              <div class="absolute inset-0 opacity-10">
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-white-500 rounded-full blur-3xl"></div>
                <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-purple-500 rounded-full blur-3xl"></div>
              </div>
              <div class="relative z-10 h-full w-full flex flex-col justify-between p-5">
                <div>
                  <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Quick Actions</h3>
                </div>
                <div class="flex flex-row w-full gap-4 text-gray-900">
                  <flux:button href="{{ route('appointments.create') }}" variant="primary"
                    class="border hover:!bg-zinc-700 font-bold">
                    <flux:icon.plus variant="mini" class="w-4 h-4 mr-1" />
                    <span>Appointment</span>
                  </flux:button>
                  <flux:button href="{{ route('patients.create') }}" variant="primary"
                    class="border hover:!bg-zinc-700 font-bold">
                    <flux:icon.user-plus variant="mini" class="w-4 h-4 mr-1" />
                    <span>Patient</span>
                  </flux:button>
                </div>
              </div>
            </div>

            <!-- System Status -->
            <div
              class="w-full p-4 md:col-span-3 lg:col-span-3 md:row-span-1 bg-white border border-gray-100 rounded-xl flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-50 rounded-lg">
                  <flux:icon.shield-check variant="solid" class="w-5 h-5 text-emerald-600" />
                </div>
                <div>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider leading-none mb-1">System Status
                  </p>
                  <p class="text-sm font-bold text-gray-900 leading-none">All Services Online</p>
                </div>
              </div>
              <div class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
            </div>
          </div>
        </div>

        <div class="flex flex-row w-full gap-4 mt-4">
          <!-- Incoming Requests (Pending Appointments) -->
          <div
            class="w-full md:col-span-6 lg:col-span-6 md:row-span-4 bg-white border border-gray-100 rounded-xl flex flex-col overflow-hidden">
            <div class="p-4 pb-4 border-b border-gray-100">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-bold text-gray-900">Incoming Requests</h2>
                  <p class="text-sm text-gray-500 font-medium">Appointments awaiting confirmation</p>
                </div>
              </div>
            </div>

            @if($upcomingAppointments->isEmpty())
              <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-gray-400">
                <div class="p-4 bg-gray-50 rounded-full mb-3">
                  <flux:icon.inbox class="w-8 h-8 text-gray-200" />
                </div>
                <p class="text-sm font-bold">No pending requests</p>
              </div>
            @else
              <div class="flex-1 overflow-auto p-5 space-y-3">
                @foreach($upcomingAppointments as $appointment)
                  <div
                    class="flex items-center gap-4 p-3 rounded-xl bg-orange-50/50 hover:bg-orange-100/50 transition-all cursor-pointer group border border-transparent hover:border-orange-200">
                    <div
                      class="flex-shrink-0 w-14 h-14 p-1.5 bg-white border border-orange-100 rounded-xl text-center shadow-sm">
                      <p class="text-[10px] font-bold uppercase leading-none text-orange-600">
                        {{ \Carbon\Carbon::parse($appointment->appt_date)->format('M') }}
                      </p>
                      <p class="text-xl text-gray-900 leading-none mt-1">
                        {{ \Carbon\Carbon::parse($appointment->appt_date)->format('d') }}
                      </p>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-black text-gray-700 truncate">
                        {{ $appointment->patient->patient_name ?? 'N/A' }}
                      </p>
                      <div class="flex items-center gap-1.5 text-xs font-medium text-gray-500 mt-0.5">
                        <span
                          class="bg-white px-1.5 py-0.5 rounded border border-gray-200">{{ $appointment->appt_time }}</span>
                        @if($appointment->appt_note)
                          <span class="truncate max-w-[150px]">• {{ $appointment->appt_note }}</span>
                        @endif
                      </div>
                    </div>
                    <flux:button wire:click="confirmAppointment({{ $appointment->appt_id }})" size="sm" variant="primary"
                      icon="check">Confirm</flux:button>
                  </div>
                @endforeach
              </div>
            @endif
          </div>

          <!-- Today's Queue -->
          <div
            class="w-full md:col-span-6 lg:col-span-6 md:row-span-4 bg-white border border-gray-100 rounded-xl flex flex-col overflow-hidden">
            <div class="p-4 pb-4 border-b border-gray-100">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-bold text-gray-900">{{ $isDoctor ? 'My Queue' : "Today's Queue" }}</h2>
                  <p class="text-sm text-gray-500">{{ $isDoctor ? 'Patients waiting for you' : 'Monitoring clinic flow' }}
                  </p>
                </div>
                <flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="$refresh"
                  class="text-gray-400 hover:text-gray-900" />
              </div>
            </div>

            @if($todayAppointments->isEmpty())
              <div class="w-full flex-1 flex flex-col items-center justify-center p-8 text-center">
                <div class="p-4 bg-gray-50 rounded-full mb-3">
                  <flux:icon.calendar class="w-8 h-8 text-gray-300" />
                </div>
                <p class="text-gray-900 font-bold">No appointments today</p>
                <p class="text-xs text-gray-400 mt-1 uppercase tracking-tighter">The schedule is clear</p>
              </div>
            @else
              <div class="w-full p-4 flex-1 overflow-auto p-5">
                <table class="w-full divide-y divide-gray-100">
                  <thead class="bg-gray-50 sticky top-0">
                    <tr>
                      <th class="py-3 pl-4 pr-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Time
                      </th>
                      <th class="px-3 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Patient</th>
                      <th class="px-3 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest text-right">
                        Status</th>
                      @if($isDoctor)
                        <th class="px-3 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Action</th>
                      @endif
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-50">
                    @foreach($todayAppointments as $appointment)
                      <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 pl-4 pr-3 whitespace-nowrap">
                          @php
                            $timeStr = $appointment->appt_time;
                            try {
                              $time = \Carbon\Carbon::parse($timeStr);
                            } catch (\Exception $e) {
                              $time = \Carbon\Carbon::parse(preg_replace('/ [AP]M$/i', '', $timeStr));
                            }
                          @endphp
                          <span class="text-sm font-bold text-gray-900">{{ $time->format('h:i A') }}</span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                          <div class="flex items-center gap-2">
                            <div
                              class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-[10px]">
                              {{ substr($appointment->patient->patient_name ?? 'NA', 0, 1) }}
                            </div>
                            <div class="flex flex-col">
                              <span
                                class="text-sm font-bold text-gray-900 leading-none mb-0.5">{{ $appointment->patient->patient_name ?? 'N/A' }}</span>
                              <span class="text-[10px] text-gray-400 font-medium italic">Dr.
                                {{ explode(' ', $appointment->doctor->user->name ?? $appointment->doctor->doctor_name ?? 'N/A')[0] }}</span>
                            </div>
                          </div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-right">
                          <flux:badge
                            :color="$appointment->appt_status === 'scheduled' ? 'zinc' : ($appointment->appt_status === 'completed' ? 'emerald' : 'orange')"
                            size="sm" variant="pill" class="font-bold uppercase tracking-tighter text-[9px]">
                            {{ ucfirst($appointment->appt_status) }}
                          </flux:badge>
                        </td>
                        @if($isDoctor)
                          <td class="px-3 py-3 whitespace-nowrap text-right">
                             <flux:button size="xs" href="{{ route('patients.show', ['patient' => $appointment->patient_id, 'tab' => 'consultation', 'appt_id' => $appointment->appt_id]) }}" icon="clipboard-document-check" variant="primary">Consult</flux:button>
                          </td>
                        @endif
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- SUPERVISOR SECTION (Single Supervisee) -->
      @if($isDoctor && $supervisee)
        <div class="pt-6 space-y-6">
          <div class="flex items-center gap-4">
            <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
              <flux:icon.users variant="solid" class="w-5 h-5" />
            </div>
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">Supervisor Dashboard</h2>
            <flux:badge color="purple" variant="soft">Overlooking 1 Staff</flux:badge>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Supervisee Profile Card -->
            <div
              class="md:col-span-1 bg-white border border-gray-100 rounded-xl p-6 flex flex-col items-center text-center">
              <div
                class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-100 to-blue-100 flex items-center justify-center text-purple-700 text-2xl font-bold mb-4 border-2 border-white shadow-sm">
                {{ substr($supervisee->user->name ?? $supervisee->doctor_name, 0, 1) }}
              </div>
              <h3 class="text-lg font-bold text-gray-900">{{ $supervisee->doctor_name }}</h3>
              <p class="text-sm text-gray-500 mb-1">{{ $supervisee->position->pos_name ?? 'Doctor' }}</p>
              <p class="text-xs text-gray-400 uppercase tracking-widest">
                {{ $supervisee->department->dept_name ?? 'General' }}
              </p>

              <div class="mt-6 w-full pt-6 border-t border-gray-50">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Status</span>
                  <flux:badge color="{{ $supervisee->status === 'ACTIVE' ? 'green' : 'orange' }}" size="sm">
                    {{ $supervisee->status ?? 'ACTIVE' }}
                  </flux:badge>
                </div>
              </div>
            </div>

            <!-- Recent Activity Feed -->
            <div class="md:col-span-2 bg-white border border-gray-100 rounded-xl p-6">
              <h3 class="font-bold text-gray-900 mb-4">Supervisee Activity</h3>
              <div class="space-y-4">
                @forelse($recentTeamActivity as $activity)
                  <a href="{{ route('consultations.view', $activity) }}" class="block group/item">
                    <div class="flex items-start gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                      <div class="mt-1">
                        <flux:icon.clipboard-document-check variant="mini" class="w-5 h-5 text-teal-500 group-hover/item:text-teal-600" />
                      </div>
                      <div>
                        <p class="text-sm text-gray-900 group-hover/item:text-blue-600 transition-colors">
                          <span class="font-bold">Dr. {{ $activity->doctor->doctor_name }}</span>
                          {{ in_array(strtolower($activity->appt_status), ['completed']) ? 'completed' : 'is consulting' }}
                          <span class="font-bold">{{ $activity->patient->patient_name }}</span>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ $activity->updated_at ? \Carbon\Carbon::parse($activity->updated_at)->diffForHumans() : 'Unknown time' }}</p>
                      </div>
                      <flux:icon.chevron-right class="w-4 h-4 text-gray-300 ml-auto opacity-0 group-hover/item:opacity-100 transition-opacity" />
                    </div>
                  </a>
                @empty
                  <div class="flex flex-col items-center justify-center py-8 text-center text-gray-400">
                    <flux:icon.clock class="w-8 h-8 opacity-20 mb-2" />
                    <p class="text-sm">No recent activity recorded.</p>
                  </div>
                @endforelse
              </div>
            </div>
          </div>
        </div>
      @endif
    </div>
  @endif
</div>

@if($isDoctor || $isAdmin)
  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
      function initTrendChart() {
        console.log('=== initTrendChart called ===');
        const ctx = document.getElementById('trendChart');

        if (!ctx) {
          console.error('Chart canvas not found');
          return;
        }
        console.log('Canvas element found:', ctx);

        if (typeof Chart === 'undefined') {
          console.error('Chart.js not loaded');
          return;
        }
        console.log('Chart.js is loaded');

        // Destroy existing chart if it exists
        const existingChart = Chart.getChart(ctx);
        if (existingChart) {
          console.log('Destroying existing chart');
          existingChart.destroy();
        }

        const trendData = @json($trendData);
        console.log('Chart data received:', trendData);
        console.log('Data structure check:', {
          hasDates: !!trendData?.dates,
          hasAppointments: !!trendData?.appointments,
          hasPatients: !!trendData?.patients,
          datesLength: trendData?.dates?.length,
          appointmentsLength: trendData?.appointments?.length,
          patientsLength: trendData?.patients?.length
        });

        // Check if data exists and has valid structure
        if (!trendData || !trendData.dates || !trendData.appointments || !trendData.patients) {
          console.error('Invalid trend data structure:', trendData);
          return;
        }

        console.log('Dates:', trendData.dates);
        console.log('Appointments:', trendData.appointments);
        console.log('Patients:', trendData.patients);

        // Calculate max values for scaling
        const maxAppointments = Math.max(...trendData.appointments, 1);
        const maxPatients = Math.max(...trendData.patients, 1);
        const maxValue = Math.max(maxAppointments, maxPatients, 5); // At least 5 for scale

        console.log('Creating chart with max value:', maxValue);

        try {
          const newChart = new Chart(ctx, {
            type: 'line',
            data: {
              labels: trendData.dates,
              datasets: [
                {
                  label: 'Appointments',
                  data: trendData.appointments,
                  borderColor: 'rgb(59, 130, 246)',
                  backgroundColor: 'rgba(59, 130, 246, 0.1)',
                  tension: 0.3,
                  fill: true,
                  pointRadius: 3,
                  pointHoverRadius: 5,
                  borderWidth: 2
                }
                // {
                //   label: 'Patients',
                //   data: trendData.patients,
                //   borderColor: 'rgb(16, 185, 129)',
                //   backgroundColor: 'rgba(16, 185, 129, 0.1)',
                //   tension: 0.3,
                //   fill: true,
                //   pointRadius: 3,
                //   pointHoverRadius: 5,
                //   borderWidth: 2
                // }
              ]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: 'rgba(0, 0, 0, 0.8)',
                  padding: 12,
                  titleColor: '#fff',
                  bodyColor: '#fff',
                  borderColor: 'rgba(255, 255, 255, 0.1)',
                  borderWidth: 1,
                  displayColors: true,
                  intersect: false,
                  mode: 'index'
                }
              },
              scales: {
                x: {
                  grid: {
                    display: false
                  },
                  ticks: {
                    color: '#6b7280',
                    font: {
                      size: 11
                    },
                    maxRotation: 45,
                    minRotation: 45
                  }
                },
                y: {
                  beginAtZero: true,
                  suggestedMax: maxValue,
                  grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                  },
                  ticks: {
                    color: '#6b7280',
                    font: {
                      size: 11
                    },
                    stepSize: 1
                  }
                }
              },
              interaction: {
                intersect: false,
                mode: 'index'
              }
            }
          });
          console.log('Chart created successfully:', newChart);
        } catch (error) {
          console.error('Error creating chart:', error);
        }
      }

      // Initialize on page load
      document.addEventListener('DOMContentLoaded', function() {
        initTrendChart();
        // Also try to initialize after a short delay to ensure DOM is ready
        setTimeout(initTrendChart, 500);
      });

      // Re-initialize on Livewire updates
      document.addEventListener('livewire:navigated', initTrendChart);

      // Handle Livewire component updates
      if (typeof Livewire !== 'undefined') {
        Livewire.hook('morph.updated', ({ component }) => {
          setTimeout(initTrendChart, 100);
        });

        Livewire.hook('component.initialized', ({ component }) => {
          setTimeout(initTrendChart, 100);
        });
      }
    </script>
  @endpush
@endif
