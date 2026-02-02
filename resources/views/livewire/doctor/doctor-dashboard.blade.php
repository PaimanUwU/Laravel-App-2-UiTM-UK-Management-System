<?php

use Livewire\Volt\Component;
use App\Models\Appointment;
use App\Models\Doctor;

new class extends Component {
    public $doctor;
    public $activeTab = 'queue';
    public $isSupervisor = false;
    public $selectedSupervisee = '';

    // Helper functions
    private function getCurrentDoctor()
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }
        
        if ($user->hasRole('system_admin')) {
            return null;
        }
        
        return $user->doctor;
    }

    private function hasDoctorProfile()
    {
        return $this->getCurrentDoctor() !== null;
    }

    public function assignDoctor($doctorId)
    {
        $targetDoctor = Doctor::find($doctorId);
        if ($targetDoctor) {
            $targetDoctor->update(['supervisor_id' => $this->doctor->doctor_id]);
            
            \Flux::toast("Dr. {$targetDoctor->doctor_name} assigned as supervisee.");
            
            // Refresh supervisor status
            $superviseeCount = Doctor::where('supervisor_id', $this->doctor->doctor_id)->count();
            $this->isSupervisor = $superviseeCount > 0;
            
            // Close modal
            $this->dispatch('close-modal', name: 'assign-supervisee-modal');
        } else {
            \Flux::toast('Doctor not found', 'error');
        }
    }

    public function mount()
    {
        if (!$this->hasDoctorProfile()) {
            session()->flash('error', 'Access denied. This page is for doctors only.');
            return redirect()->route('dashboard');
        }
        
        $this->doctor = $this->getCurrentDoctor();

        if ($this->doctor) {
            $superviseeCount = Doctor::where('supervisor_id', $this->doctor->doctor_id)->count();
            $this->isSupervisor = $superviseeCount > 0;
        }
    }

    public function toggleStatus()
    {
        if (!$this->hasDoctorProfile()) {
            session()->flash('error', 'Only doctors can change their status.');
            return;
        }
        
        $newStatus = $this->doctor->status === 'ACTIVE' ? 'BREAK' : 'ACTIVE';
        $this->doctor->update(['status' => $newStatus]);

        \Flux::toast("Status updated to {$newStatus}");
    }

    public function with(): array
    {
        if (!$this->doctor) {
            return [
                'queue' => collect(),
                'supervisees' => collect(),
                'recentConsultations' => collect(),
                'availableDoctors' => collect(),
            ];
        }

        $data = [
            'queue' => Appointment::with('patient')
                ->where('doctor_id', $this->doctor->doctor_id)
                ->where('appt_date', now()->format('Y-m-d'))
                ->whereIn('appt_status', ['CONFIRMED', 'Confirmed', 'ARRIVED', 'Arrived', 'CONSULTING', 'Consulting'])
                ->orderBy('appt_time')
                ->get(),
        ];

        if ($this->isSupervisor) {
            $supervisees = Doctor::where('supervisor_id', $this->doctor->doctor_id)->pluck('doctor_id');
            $data['supervisees'] = Doctor::whereIn('doctor_id', $supervisees)->get();

            $query = Appointment::with(['doctor', 'patient'])
                ->whereIn('doctor_id', $supervisees)
                ->whereIn('appt_status', ['COMPLETED', 'CONSULTING']);

            if ($this->selectedSupervisee) {
                $query->where('doctor_id', $this->selectedSupervisee);
            }

            $data['recentConsultations'] = $query->latest('updated_at')
                ->take(10)
                ->get();
        } else {
            $data['supervisees'] = collect();
            $data['recentConsultations'] = collect();
        }

        // Fetch available doctors
        $data['availableDoctors'] = Doctor::where('doctor_id', '!=', $this->doctor->doctor_id)
            ->where('status', 'ACTIVE')
            ->orderBy('doctor_name')
            ->get();

        return $data;
    }
};
?>

<div class="space-y-6">
    @if (!$doctor)
        <flux:card class="p-8 text-center space-y-4">
            <div class="flex justify-center">
                <div class="p-4 bg-orange-50 rounded-full">
                    <flux:icon.user-minus class="w-12 h-12 text-orange-500" />
                </div>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Profile Not Linked</h2>
            <p class="text-gray-600 max-w-md mx-auto">
                Your user account is assigned the <strong>doctor</strong> role, but it is not linked to a physical doctor
                profile in our records.
            </p>
            <div class="pt-4">
                <flux:button variant="primary" href="{{ route('dashboard') }}">Return to Main Dashboard</flux:button>
            </div>
            <p class="text-xs text-gray-400">Please contact the system administrator to link your account.</p>
        </flux:card>
    @else
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dr. {{ $doctor->doctor_name }}</h1>
                <p class="text-sm text-gray-600">{{ $doctor->position->position_name ?? 'Doctor' }} •
                    {{ $doctor->department->dept_name ?? 'General' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-600">Workload Status:</span>
                <flux:switch wire:click="toggleStatus" :checked="$doctor->status === 'ACTIVE'" />
                <flux:badge color="{{ $doctor->status === 'ACTIVE' ? 'green' : 'orange' }}">
                    {{ $doctor->status }}
                </flux:badge>
            </div>
        </div>

        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'queue')"
                    class="{{ $activeTab === 'queue' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                    <flux:icon.user-group variant="mini" class="w-5 h-5 mr-2" />
                    My Queue
                </button>

                <button wire:click="$set('activeTab', 'oversight')"
                    class="{{ $activeTab === 'oversight' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                    <flux:icon.eye variant="mini" class="w-5 h-5 mr-2" />
                    Team Oversight
                </button>
            </nav>
        </div>

        <div class="mt-6">
            @if ($activeTab === 'queue')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <flux:card class="p-4 md:col-span-1 space-y-4">
                        <div class="flex items-center gap-2">
                            <flux:icon.user-group variant="mini" class="text-blue-500" />
                            <h3 class="font-semibold">Today's Load</h3>
                        </div>
                        <div class="text-3xl font-bold">{{ $queue->count() }}</div>
                        <p class="text-sm text-gray-600">Patients remaining</p>
                    </flux:card>

                    <flux:card class="p-4 md:col-span-2 space-y-4">
                        <h3 class="font-semibold">My Queue</h3>
                        @if ($queue->isEmpty())
                            <p class="text-gray-600 italic">No active patients assigned to you.</p>
                        @else
                            <div class="space-y-3">
                                @foreach ($queue as $appt)
                                    <div
                                        class="flex items-center justify-between p-3 border rounded-lg {{ $appt->appt_status === 'CONSULTING' ? 'border-green-400 bg-green-50' : 'border-gray-100' }}">
                                        <div class="flex items-center gap-3">
                                            <div class="font-mono font-bold text-gray-600">{{ $appt->appt_time }}</div>
                                            <div>
                                                <div class="font-semibold">{{ $appt->patient->patient_name }}</div>
                                                <div class="text-xs text-gray-600">Ticket #{{ $appt->appt_id }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if ($appt->appt_status === 'CONSULTING')
                                                <flux:button size="sm" variant="primary"
                                                    href="{{ route('patients.show', ['patient' => $appt->patient->patient_id, 'tab' => 'consultation', 'appt_id' => $appt->appt_id]) }}">Continue
                                                </flux:button>
                                            @else
                                                <flux:button size="sm" href="{{ route('patients.show', ['patient' => $appt->patient->patient_id, 'tab' => 'consultation', 'appt_id' => $appt->appt_id]) }}">Start
                                                </flux:button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </flux:card>
                </div>
            @endif

            @if($activeTab === 'oversight')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:card class="p-4 ">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold">Team Status</h3>
                                <flux:modal.trigger name="assign-supervisee-modal">
                                    <flux:button size="sm" icon="plus">Add Member</flux:button>
                                </flux:modal.trigger>
                            </div>
                            <div class="space-y-4">
                                @foreach($supervisees as $doc)
                                    <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <flux:avatar
                                                src="https://ui-avatars.com/api/?name={{ urlencode($doc->doctor_name) }}&color=7F9CF5&background=EBF4FF" />
                                            <div>
                                                <div class="font-medium">Dr. {{ $doc->doctor_name }}</div>
                                                <div class="text-xs text-gray-600">{{ $doc->position->position_name ?? 'Doctor' }}
                                                </div>
                                            </div>
                                        </div>
                                        <flux:badge color="{{ $doc->status === 'ACTIVE' ? 'green' : 'orange' }}">
                                            {{ $doc->status }}
                                        </flux:badge>
                                    </div>
                                @endforeach
                                @if($supervisees->isEmpty())
                                    <p class="text-gray-600 italic">No supervisees assigned.</p>
                                @endif
                            </div>
                        </flux:card>

                        <flux:card class="p-4 ">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold">Recent Team Activity</h3>
                                <div class="w-48">
                                    <flux:select wire:model.live="selectedSupervisee" placeholder="All Supervisees" size="sm">
                                        <flux:select.option value="">All Supervisees</flux:select.option>
                                        @foreach($supervisees as $doc)
                                            <flux:select.option value="{{ $doc->doctor_id }}">{{ $doc->doctor_name }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>
                            </div>
                            <div class="space-y-3">
                                @foreach($recentConsultations as $case)
                                    <div class="flex items-start gap-3 text-sm">
                                        <div class="mt-1">
                                            <flux:icon.clipboard-document-check variant="mini" class="text-teal-500" />
                                        </div>
                                        <div>
                                            <span class="font-medium">Dr. {{ $case->doctor->doctor_name }}</span>
                                            {{ $case->appt_status === 'COMPLETED' ? 'completed' : 'is treating' }}
                                            patient
                                            <span class="font-medium">{{ $case->patient->patient_name }}</span>
                                            <div class="text-xs text-gray-600">{{ $case->updated_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                @endforeach
                                @if($recentConsultations->isEmpty())
                                    <p class="text-gray-600 italic">No recent consultations found.</p>
                                @endif
                            </div>
                        </flux:card>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <flux:modal name="assign-supervisee-modal" class="min-w-[500px]">
        <div class="space-y-6">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Assign Supervisee</h2>
                <p class="text-sm text-gray-500">Select a doctor to add to your supervision team.</p>
            </div>

            <div class="space-y-3">
                @forelse($availableDoctors as $d)
                    <button
                        type="button"
                        onclick="if(confirm('Are you sure you want to assign Dr. {{ $d->doctor_name }} as your supervisee?')) { @this.call('assignDoctor', {{ $d->doctor_id }}) }"
                        class="w-full p-4 border-2 rounded-lg text-left transition-all hover:shadow-md
                            border-gray-200 hover:border-gray-300"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-600 font-semibold">
                                        {{ substr($d->doctor_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">Dr. {{ $d->doctor_name }}</div>
                                    <div class="text-sm text-gray-600">
                                        {{ $d->position->position_name ?? 'Doctor' }} • 
                                        {{ $d->department?->dept_name ?? 'General' }}
                                    </div>
                                    <div class="text-xs text-gray-400">ID: {{ $d->doctor_id }}</div>
                                </div>
                            </div>
                            @if($d->supervisor_id)
                                <flux:badge color="orange" size="sm">Has Supervisor</flux:badge>
                            @endif
                        </div>
                    </button>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <p class="font-medium">No available doctors</p>
                        <p class="text-sm mt-1">All active doctors are already assigned.</p>
                    </div>
                @endforelse
            </div>
            
            <div class="flex justify-end gap-2 pt-4 border-t">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>