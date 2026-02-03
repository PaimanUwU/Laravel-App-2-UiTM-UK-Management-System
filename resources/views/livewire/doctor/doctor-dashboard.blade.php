<?php

use Livewire\Volt\Component;
use App\Models\Appointment;
use App\Models\Doctor;

new class extends Component {
    public $doctor;
    public $selectedSupervisee = '';
    public $showAvailableDoctors = false;

    // Helper functions
    private function getCurrentDoctor()
    {
        $user = auth()->user();

        if (!$user) {
            \Log::info('No authenticated user');
            return null;
        }

        if ($user->hasRole('system_admin')) {
            \Log::info('User is system admin');
            return null;
        }

        \Log::info('Getting doctor for user', [
            'userId' => $user->id,
            'userName' => $user->name,
            'userRoles' => $user->roles->pluck('name')->toArray()
        ]);

        $doctor = $user->doctor;

        \Log::info('Doctor relationship result', [
            'doctorFound' => $doctor ? 'Yes' : 'No',
            'doctorId' => $doctor?->doctor_id, // FIXED: lowercase attribute
            'doctorName' => $doctor?->doctor_name
        ]);

        return $doctor;
    }

    private function hasDoctorProfile()
    {
        return $this->getCurrentDoctor() !== null;
    }

    public function assignDoctor(int $doctorId = null)
    {
        if ($doctorId === null) {
            \Flux::toast('Invalid doctor ID', 'error');
            return;
        }

        // Ensure we have the current doctor
        $currentDoctor = $this->getCurrentDoctor();
        if (!$currentDoctor) {
            \Flux::toast('Unable to identify current doctor', 'error');
            \Log::error('Current doctor not found in assignDoctor');
            return;
        }

        \Log::info('assignDoctor called', [
            'doctorId' => $doctorId,
            'currentDoctorId' => $currentDoctor->doctor_id,
            'currentDoctorName' => $currentDoctor->doctor_name
        ]);

        $targetDoctor = Doctor::find($doctorId);
        \Log::info('targetDoctor found', [
            'found' => $targetDoctor ? 'Yes' : 'No',
            'targetDoctorId' => $targetDoctor?->doctor_id, // FIXED
            'targetDoctorName' => $targetDoctor?->doctor_name,
            'currentSupervisorId' => $targetDoctor?->supervisor_id // CORRECT: matches DB column
        ]);

        if ($targetDoctor) {
            // Remove existing supervisee
            $existingSupervisee = $this->doctor->supervisees()->first();
            if ($existingSupervisee) {
                $existingSupervisee->update(['supervisor_id' => null]);
                \Log::info('Removed existing supervisee', ['existingSuperviseeId' => $existingSupervisee->doctor_id]); // FIXED
            }

            // Prevent self-supervision
            if ($targetDoctor->doctor_id == $currentDoctor->doctor_id) {
                \Flux::toast('You cannot assign yourself as your own supervisee', 'error');
                return;
            }

            // Check duplicate assignment
            if ($targetDoctor->supervisor_id == $currentDoctor->doctor_id) { // LOOSE COMPARISON
                \Flux::toast("Dr. {$targetDoctor->doctor_name} is already your supervisee.", 'warning');
                return;
            }

            // Update supervisor_id
            $targetDoctor->supervisor_id = $currentDoctor->doctor_id;
            $updated = $targetDoctor->save();

            \Log::info('supervisor update attempt', [
                'newSupervisorId' => $targetDoctor->fresh()->supervisor_id,
                'currentDoctorId' => $currentDoctor->doctor_id // FIXED
            ]);

            if ($updated) {
                \Flux::toast("Dr. {$targetDoctor->doctor_name} assigned as supervisee.");
            } else {
                \Flux::toast('Failed to assign supervisee', 'error');
            }

            // Hide the available doctors list after assignment
            $this->showAvailableDoctors = false;
        } else {
            \Log::error('Doctor not found', ['doctorId' => $doctorId]);
            \Flux::toast('Doctor not found', 'error');
        }
    }

    public function removeSupervisee(int $doctorId)
    {
        \Log::info('removeSupervisee called', ['doctorId' => $doctorId]);

        if ($doctorId === null) {
            \Flux::toast('Invalid doctor ID', 'error');
            return;
        }

        // Ensure we have the current doctor
        $currentDoctor = $this->getCurrentDoctor();
        if (!$currentDoctor) {
            \Flux::toast('Unable to identify current doctor', 'error');
            return;
        }

        \Log::info('Current doctor found', ['doctorId' => $currentDoctor->doctor_id]);

        $targetDoctor = Doctor::find($doctorId);
        \Log::info('Target doctor found', [
            'found' => $targetDoctor ? 'Yes' : 'No',
            'supervisorId' => $targetDoctor?->supervisor_id,
            'currentDoctorId' => $currentDoctor->doctor_id
        ]);

        if ($targetDoctor && $targetDoctor->supervisor_id == $currentDoctor->doctor_id) {
            // Remove the supervisor_id from the supervisee using save() method
            $targetDoctor->supervisor_id = null;
            $updated = $targetDoctor->save();

            \Log::info('Update attempt', [
                'updated' => $updated,
                'newSupervisorId' => $targetDoctor->fresh()->supervisor_id,
                'targetDoctorId' => $targetDoctor->doctor_id
            ]);

            if ($updated) {
                \Flux::toast("Dr. {$targetDoctor->doctor_name} removed from supervision.");
            } else {
                \Flux::toast('Failed to remove supervisee', 'error');
            }
        } else {
            \Flux::toast('Unable to remove supervisee - not under your supervision', 'error');
        }
    }

    public function mount()
    {
        if (!$this->hasDoctorProfile()) {
            session()->flash('error', 'Access denied. This page is for doctors only.');
            return redirect()->route('dashboard');
        }

        $this->doctor = $this->getCurrentDoctor();

        \Log::info('Doctor mount', [
            'doctorFound' => $this->doctor ? 'Yes' : 'No',
            'doctorId' => $this->doctor?->doctor_id, // FIXED
            'doctorName' => $this->doctor?->doctor_name,
            'userId' => auth()->user()->id
        ]);
    }

    public function toggleStatus()
    {
        if (!$this->hasDoctorProfile()) {
            session()->flash('error', 'Only doctors can change their status.');
            return;
        }

        $currentDoctor = $this->getCurrentDoctor();
        if (!$currentDoctor) {
            \Flux::toast('Unable to identify current doctor', 'error');
            return;
        }

        $newStatus = $currentDoctor->status === 'ACTIVE' ? 'BREAK' : 'ACTIVE';
        $currentDoctor->update(['status' => $newStatus]);

        \Flux::toast("Status updated to {$newStatus}");
    }

    public function with(): array
    {
        $currentDoctor = $this->getCurrentDoctor();
        if (!$currentDoctor) {
            return [
                'queue' => collect(),
                'supervisees' => collect(),
                'recentConsultations' => collect(),
                'availableDoctors' => collect(),
            ];
        }

        $data = [
            'queue' => Appointment::with('patient')
                ->where('doctor_id', $currentDoctor->doctor_id) // FIXED: attribute + column match
                ->where('appt_date', now()->format('Y-m-d'))
                ->whereIn('appt_status', ['ASSIGNED', 'DISCHARGED', 'FOLLOW_UP', 'CANCEL'])
                ->orderBy('appt_time')
                ->get(),
        ];

        // Fetch supervisee data if any exist
        $supervisees = Doctor::with(['user', 'department', 'position'])
            ->where('supervisor_id', $currentDoctor->doctor_id) // FIXED
            ->get();
        $data['supervisees'] = $supervisees;

        \Log::info('Supervisee query', [
            'currentDoctorId' => $currentDoctor->doctor_id, // FIXED
            'superviseesCount' => $supervisees->count(),
            'supervisees' => $supervisees->pluck('doctor_name', 'doctor_id')->toArray() // FIXED
        ]);

        if ($supervisees->isNotEmpty()) {
            // Get recent consultations for all supervisees
            $superviseeIds = $supervisees->pluck('doctor_id'); // FIXED
            $query = Appointment::with(['doctor', 'patient'])
                ->whereIn('doctor_id', $superviseeIds)
                ->whereIn('appt_status', ['ASSIGNED', 'DISCHARGED', 'FOLLOW_UP', 'CANCEL']);

            $data['recentConsultations'] = $query->latest('updated_at')
                ->take(10)
                ->get();
        } else {
            $data['recentConsultations'] = collect();
        }

        // CRITICAL QUERY FIX: Column names must match DB schema exactly
        $availableDoctors = Doctor::where('doctor_id', '!=', $currentDoctor->doctor_id) // FIXED: lowercase column
            ->where('status', 'ACTIVE')
            ->whereNull('supervisor_id') // CORRECT: matches DB column casing
            ->orderBy('doctor_name')
            ->get();

        \Log::info('Available doctors', [
            'currentDoctorId' => $currentDoctor->doctor_id, // FIXED
            'count' => $availableDoctors->count(),
            'doctors' => $availableDoctors->pluck('doctor_name', 'doctor_id')->toArray() // FIXED
        ]);

        $data['availableDoctors'] = $availableDoctors;

        return $data;
    }
}; ?>

<div>
    @if($this->hasDoctorProfile())
        <!-- Combined Doctor & Queue Command Center -->
        {{-- <flux:card class="p-0 overflow-hidden mb-8 border-none shadow-sm"> --}}
            {{-- <div class="flex flex-row lg:flex-row">
                <!-- Sidebar: Doctor Profile & Status -->
                {{-- <div class="w-full lg:w-80 bg-gray-50/80 p-6 flex-none border-b lg:border-b-0 lg:border-r border-gray-100">
                    <div class="flex flex-col items-center text-center space-y-4">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-md">
                            {{ substr($doctor->doctor_name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Dr. {{ $doctor->doctor_name }}</h2>
                            <p class="text-sm text-gray-600 font-medium">{{ $doctor->position->position_name ?? 'Doctor' }}</p>
                            <div class="flex items-center justify-center gap-2 mt-2">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $doctor->status === 'ACTIVE' ? 'green' : 'orange' }}-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-{{ $doctor->status === 'ACTIVE' ? 'green' : 'orange' }}-500"></span>
                                </span>
                                <span class="text-xs font-semibold uppercase tracking-wider text-{{ $doctor->status === 'ACTIVE' ? 'green' : 'orange' }}-600">
                                    {{ $doctor->status }}
                                </span>
                            </div>
                        </div>

                        <div class="w-full pt-4 space-y-3">
                            <flux:button wire:click="toggleStatus" class="w-full" size="sm" variant="{{ $doctor->status === 'ACTIVE' ? 'outline' : 'filled' }}">
                                {{ $doctor->status === 'ACTIVE' ? 'Go on Break' : 'Resume Service' }}
                            </flux:button>
                        </div>

                        <div class="w-full pt-6 text-left space-y-2">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Department</h4>
                            <p class="text-sm text-gray-700 font-medium">{{ $doctor->department->dept_name ?? 'General Practice' }}</p>
                        </div>
                    </div>
                </div> --}}

                <!-- Main: Today's Queue -->
                {{-- <div class="flex-1 p-6 lg:p-8 min-w-0">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                <flux:icon.calendar-days variant="solid" class="w-5 h-5" />
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Today's Patient Queue</h3>
                        </div>
                        <flux:badge color="blue" variant="soft" size="sm">{{ $queue->count() }} Waiting</flux:badge>
                    </div>

                    <div class="space-y-3">
                        @forelse($queue as $appointment)
                            <div class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-xl hover:border-blue-200 hover:shadow-sm transition-all duration-200 group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        <flux:icon.user class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 group-hover:text-blue-700 transition-colors">{{ $appointment->patient->patient_name }}</h4>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <flux:icon.clock class="w-3.5 h-3.5 text-gray-400" />
                                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($appointment->appt_time)->format('h:i A') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <flux:badge color="{{ $appointment->appt_status === 'CONSULTING' ? 'blue' : 'green' }}" size="xs" variant="soft">
                                        {{ $appointment->appt_status }}
                                    </flux:badge>
                                    <flux:button size="sm" href="{{ route('patients.show', ['patient' => $appointment->patient_id, 'tab' => 'consultation', 'appt_id' => $appointment->appt_id]) }}" variant="ghost" square>
                                        <flux:icon.chevron-right class="w-4 h-4" />
                                    </flux:button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 bg-gray-50/50 rounded-2xl border-2 border-dashed border-gray-100">
                                <flux:icon.calendar-days class="w-12 h-12 text-gray-200 mx-auto mb-3" />
                                <h4 class="text-gray-900 font-bold">Zero Appointments</h4>
                                <p class="text-sm text-gray-500 mt-1">Your schedule is currently clear for the day.</p>
                            </div>
                        @endforelse
                    </div>
                </div> --}}
            {{-- </div> --}}
        {{-- </flux:card> --}}

            {{-- <hr class="border-gray-100"> --}}

            <!-- SUPERVISOR DASHBOARD SECTION -->
            <section class="space-y-6">
                <div class="flex items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Team Oversight</h1>
                        <p class="text-sm text-gray-600">Overview for doctors supervising.</p>
                    </div>
                    {{-- <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                        <flux:icon.users variant="solid" class="w-5 h-5" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-tight">Team Oversight</h2> --}}
                    <flux:badge color="purple" variant="soft">Overlooking {{ $supervisees->count() }} Staff</flux:badge>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Combined Supervisee Profile & Team Status Card -->
                    <div class="md:col-span-1 bg-white border border-gray-100 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-900">Team Members</h3>
                        </div>

                        <!-- Add New Supervisee Button -->
                        <div class="mb-4">
                            <flux:button
                                wire:click="$toggle('showAvailableDoctors')"
                                variant="outline"
                                size="sm"
                                icon="plus"
                            >
                                Add New Supervisee
                            </flux:button>
                        </div>

                        <!-- Available Doctors List -->
                        @if($showAvailableDoctors)
                            <div class="space-y-3 mb-6">
                                <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Available Doctors</h5>
                                @if($availableDoctors->isNotEmpty())
                                    @foreach($availableDoctors as $doctor)
                                        <button
                                            type="button"
                                            onclick="if(confirm('Are you sure you want to assign Dr. {{ $doctor->doctor_name }} as your supervisee?')) { @this.call('assignDoctor', {{ $doctor->doctor_id }}); }"
                                            class="w-full flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-left"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-blue-600 font-semibold">
                                                        {{ substr($doctor->doctor_name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900">Dr. {{ $doctor->doctor_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $doctor->position->position_name ?? 'Doctor' }}</div>
                                                </div>
                                            </div>
                                            <flux:icon.arrow-right class="w-4 h-4 text-gray-400" />
                                        </button>
                                    @endforeach
                                @else
                                    <div class="text-center py-4 text-gray-500">
                                        <flux:icon.user-minus class="w-6 h-6 mx-auto mb-2 opacity-50" />
                                        <p class="text-sm">No available doctors to assign</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Current Supervisees Profiles -->
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h5 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Current Supervisees</h5>
                            <div class="space-y-3">
                                @forelse($supervisees as $supervisee)
                                    <button
                                        type="button"
                                        onclick="if(confirm('Are you sure you want to remove Dr. {{ $supervisee->doctor_name }} from your supervision?')) { @this.call('removeSupervisee', {{ $supervisee->doctor_id }}); }"
                                        class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 hover:bg-red-50 transition-colors text-left"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                    <span class="text-purple-600 font-semibold">
                                                        {{ substr($supervisee->doctor_name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900">Dr. {{ $supervisee->doctor_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $supervisee->position->position_name ?? 'Doctor' }}</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <flux:badge size="xs" color="{{ $supervisee->status === 'ACTIVE' ? 'green' : 'orange' }}">
                                                    {{ $supervisee->status }}
                                                </flux:badge>
                                                <flux:icon.trash class="w-4 h-4 text-red-500" />
                                            </div>
                                        </div>
                                    </button>
                                @empty
                                    <div class="text-center py-6 text-gray-400">
                                        <flux:icon.users class="w-8 h-8 mx-auto mb-2 opacity-20" />
                                        <p class="text-sm">No supervisees assigned</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Feed -->
                    <div class="md:col-span-2 bg-white border border-gray-100 rounded-xl p-6">
                        <h3 class="font-bold text-gray-900 mb-4">
                            All Supervisee Activity
                        </h3>
                        <div class="space-y-4">
                            @forelse($recentConsultations as $activity)
                                <a href="{{ route('consultations.view', $activity) }}" class="block group/item">
                                    <div class="flex items-start gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                                        <div class="mt-1">
                                            <flux:icon.clipboard-document-check variant="mini" class="w-5 h-5 text-teal-500 group-hover/item:text-teal-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-900 group-hover/item:text-blue-600 transition-colors">
                                                <span class="font-bold">Dr. {{ $activity->doctor->doctor_name }}</span>
                                                {{ in_array(strtolower($activity->appt_status), ['DISCHARGED']) ? 'discharged' : 'working' }}
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
            </section>
        </div>
    @endif
</div>
