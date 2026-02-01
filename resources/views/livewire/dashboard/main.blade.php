<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;

new class extends Component {
  public function with()
  {
    $user = auth()->user();
    $isDoctor = $user->hasRole('doctor');
    $doctor = $isDoctor ? $user->doctor : null;

    if ($isDoctor && $doctor) {
      $todayAppointments = Appointment::with(['patient', 'doctor'])
        ->where('doctor_ID', $doctor->doctor_ID)
        ->whereDate('appt_date', Carbon::today())
        ->orderBy('appt_time')
        ->get();

      $upcomingAppointments = Appointment::with(['patient', 'doctor'])
        ->where('doctor_ID', $doctor->doctor_ID)
        ->whereDate('appt_date', '>', Carbon::today())
        ->orderBy('appt_date')
        ->orderBy('appt_time')
        ->take(5)
        ->get();

      $totalPatients = Appointment::where('doctor_ID', $doctor->doctor_ID)
        ->distinct('patient_ID')
        ->count('patient_ID');

      $supervisees = $doctor->supervisees()->with(['user', 'department', 'position'])->get();

      return [
        'isDoctor' => true,
        'doctor' => $doctor,
        'totalPatients' => $totalPatients,
        'todayAppointmentsCount' => $todayAppointments->count(),
        'pendingAppointmentsCount' => Appointment::where('doctor_ID', $doctor->doctor_ID)
          ->where('appt_status', 'scheduled')
          ->count(),
        'todayAppointments' => $todayAppointments,
        'upcomingAppointments' => $upcomingAppointments,
        'supervisees' => $supervisees,
      ];
    }

    // Default/Admin View
    return [
      'isDoctor' => false,
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
      'supervisees' => collect(),
    ];
  }
}; ?>

<div class="space-y-6">
  @if($isDoctor)
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">My Dashboard</h1>
        <p class="text-gray-500 font-medium">Welcome back, Dr. {{ explode(' ', auth()->user()->name)[0] }}</p>
      </div>
      <div class="flex items-center gap-2">
        <flux:badge color="blue" variant="solid" class="uppercase tracking-widest font-bold">Doctor Account</flux:badge>
      </div>
    </div>
  @endif

  <!-- Bento Grid Layout -->
  <div class="flex flex-col w-full gap-4 ">

    <div class="flex flex-col w-full gap-4">

        <div class="flex flex-row w-full gap-4">
            <!-- Total Patients - Medium Square (2x2) -->
            <div
              class="w-full md:col-span-3 lg:col-span-3 md:row-span-2 bg-white border border-gray-100 rounded-xl p-5 flex flex-col justify-between">
              <div class="flex items-start justify-between">
                <div class="p-3 bg-blue-50 rounded-lg text-blue-700">
                  <flux:icon.users variant="solid" class="w-6 h-6" />
                </div>
                {{-- <span class="text-xs font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">+12%</span> --}}
              </div>
              <div class="p-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">
                  {{ $isDoctor ? 'My Patients' : 'Total Patients' }}
                </p>
                <p class="text-4xl font-black text-gray-900">{{ $totalPatients }}</p>
              </div>
            </div>

            <!-- Today's Appointments - Small Square (1x2) -->
            <div
              class="w-full md:col-span-3 lg:col-span-2 md:row-span-2 bg-white border border-gray-100 rounded-xl p-5 flex flex-col justify-between">
              <div class="flex items-start justify-between">
                <div class="p-3 bg-emerald-50 rounded-lg text-emerald-700">
                  <flux:icon.calendar variant="solid" class="w-6 h-6" />
                </div>
              </div>
              <div class="p-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ $isDoctor ? 'My Appts' : 'Today' }}</p>
                <p class="text-4xl font-black text-gray-900 mb-1">{{ $todayAppointmentsCount }}</p>
              </div>
            </div>

            <!-- Pending Appointments - Small Square (2x1) -->
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
            <!-- Quick Actions - Wide Rectangle (4x2) -->
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
                </div>zzzzo
            </div>

            <!-- System Status - Small Rectangle (3x1) -->
            <div
                class="w-full p-4 md:col-span-3 lg:col-span-3 md:row-span-1 bg-white border border-gray-100 rounded-xl flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-50 rounded-lg">
                    <flux:icon.shield-check variant="solid" class="w-5 h-5 text-emerald-600" />
                    </div>
                    <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider leading-none mb-1">System Status lo</p>
                    <p class="text-sm font-bold text-gray-900 leading-none">All Services Online</p>
                    </div>
                </div>
                <div class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
            </div>

        </div>
    </div>

    <div class="flex flex-row w-full gap-4 mt-4">
        <!-- Upcoming Appointments - Tall Panel (6x4) -->
        <div
          class="w-full md:col-span-6 lg:col-span-6 md:row-span-4 bg-white border border-gray-100 rounded-xl flex flex-col overflow-hidden">
          <div class="p-4 pb-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-bold text-gray-900">Upcoming Arrivals</h2>
                <p class="text-sm text-gray-500 font-medium">Coming up this week</p>
              </div>
            </div>
          </div>

          @if($upcomingAppointments->isEmpty())
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-gray-400">
              <div class="p-4 bg-gray-50 rounded-full mb-3">
                <flux:icon.calendar class="w-8 h-8 text-gray-200" />
              </div>
              <p class="text-sm font-bold">No upcoming appointments</p>
            </div>
          @else
            <div class="flex-1 overflow-auto p-5 space-y-3">
              @foreach($upcomingAppointments as $appointment)
                <div
                  class="flex items-center gap-4 p-3 rounded-xl bg-gray-50/50 hover:bg-gray-100/50 transition-all cursor-pointer group border border-transparent hover:border-gray-200">
                  <div class="flex-shrink-0 w-14 h-14 p-1.5 bg-zinc-200 rounded-xl text-white text-center shadow-sm">
                    <p class="text-[10px] font-bold uppercase leading-none text-gray-700">
                      {{ \Carbon\Carbon::parse($appointment->appt_date)->format('M') }}
                    </p>
                    <p class="text-xl text-gray-700 leading-none mt-1">
                      {{ \Carbon\Carbon::parse($appointment->appt_date)->format('d') }}
                    </p>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-black text-gray-700 truncate">
                      {{ $appointment->patient->patient_name ?? 'N/A' }}
                    </p>
                    @php
                      try {
                        $time = \Carbon\Carbon::parse($appointment->appt_time);
                      } catch (\Exception $e) {
                        $time = \Carbon\Carbon::parse(preg_replace('/ [AP]M$/i', '', $appointment->appt_time));
                      }
                    @endphp
                    <div class="flex items-center gap-1.5 text-xs font-bold text-gray-400 mt-0.5 uppercase tracking-tighter">
                      <flux:icon.clock variant="mini" class="w-3 h-3" />
                      <span>{{ $time->format('h:i A') }}</span>
                    </div>
                  </div>
                  <flux:icon.chevron-right
                    class="w-4 h-4 text-gray-300 group-hover:text-gray-900 group-hover:translate-x-0.5 transition-all flex-shrink-0" />
                </div>
              @endforeach
            </div>
          @endif
        </div>

        <!-- Today's Queue - Large Panel (6x4) -->
        <div
          class="w-full md:col-span-6 lg:col-span-6 md:row-span-4 bg-white border border-gray-100 rounded-xl flex flex-col overflow-hidden">
          <div class="p-4 pb-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $isDoctor ? 'My Queue' : "Today's Queue" }}</h2>
                <p class="text-sm text-gray-500">{{ $isDoctor ? 'Patients waiting for you' : 'Monitoring clinic flow' }}</p>
              </div>
              <flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="$refresh"
                class="text-gray-400 hover:text-gray-900" />
            </div>
          </div ">

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
                    <th class="py-3 pl-4 pr-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Time</th>
                    <th class="px-3 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Patient</th>
                    <th class="px-3 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest text-right">
                      Status</th>
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
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
    </div>

  </div>

  @if($isDoctor && $supervisees->isNotEmpty())
    <div class="pt-6 space-y-4">
      <div class="flex items-center gap-4">
        <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
          <flux:icon.users variant="solid" class="w-5 h-5" />
        </div>
        <h2 class="text-xl font-bold text-gray-900 tracking-tight">Supervisor Dashboard</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($supervisees as $supervisee)
          <div
            class="bg-white border border-gray-100 rounded-xl p-4 flex items-center justify-between group hover:border-purple-200 transition-all hover:shadow-sm">
            <div class="flex items-center gap-3">
              <div
                class="w-11 h-11 rounded-full bg-gradient-to-br from-purple-50 to-blue-50 flex items-center justify-center text-purple-700 font-bold border border-purple-100">
                {{ substr($supervisee->user->name ?? $supervisee->doctor_name, 0, 1) }}
              </div>
              <div>
                <p class="text-sm font-bold text-gray-900 leading-none mb-1">
                  {{ $supervisee->user->name ?? $supervisee->doctor_name }}</p>
                <div class="flex flex-col gap-0.5">
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider leading-none">
                    {{ $supervisee->position->pos_name ?? 'Medical Officer' }}</p>
                  <p class="text-[10px] text-gray-400 italic leading-none">
                    {{ $supervisee->department->dept_name ?? 'General Clinic' }}</p>
                </div>
              </div>
            </div>
            <flux:button size="sm" variant="ghost" icon="chevron-right" class="text-gray-300 group-hover:text-purple-600" />
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>
