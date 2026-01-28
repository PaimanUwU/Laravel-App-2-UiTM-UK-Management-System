<?php

use Livewire\Volt\Component;
use App\Models\Doctor;
use App\Models\Appointment;

new class extends Component {
    public $supervisor;

    public function mount()
    {
        $this->supervisor = Doctor::where('user_id', auth()->id())->first();

        if (!$this->supervisor) {
            abort(403, 'Access Denied: Not a doctor profile.');
        }
    }

    public function with(): array
    {
        $supervisees = Doctor::where('supervisor_id', $this->supervisor->doctor_id)->pluck('doctor_id');

        return [
            'supervisees' => Doctor::whereIn('doctor_id', $supervisees)->get(),
            'recentConsultations' => Appointment::with(['doctor', 'patient'])
                ->whereIn('doctor_id', $supervisees)
                ->whereIn('appt_status', ['COMPLETED', 'CONSULTING'])
                ->latest('updated_at')
                ->take(10)
                ->get(),
        ];
    }
};
?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Supervisor Dashboard</h1>
        <p class="text-sm text-gray-600">Overseeing {{ count($supervisees) }} medical staff members.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Team Status -->
        <flux:card>
            <h3 class="font-semibold mb-4">Team Status</h3>
            <div class="space-y-4">
                @foreach($supervisees as $doc)
                    <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg">
                        <div class="flex items-center gap-3">
                            <flux:avatar
                                src="https://ui-avatars.com/api/?name={{ urlencode($doc->doctor_name) }}&color=7F9CF5&background=EBF4FF" />
                            <div>
                                <div class="font-medium">Dr. {{ $doc->doctor_name }}</div>
                                <div class="text-xs text-gray-600">{{ $doc->position->position_name ?? 'Doctor' }}</div>
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

        <!-- Recent Activity -->
        <flux:card>
            <h3 class="font-semibold mb-4">Recent Team Activity</h3>
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