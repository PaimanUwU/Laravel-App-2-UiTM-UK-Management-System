<?php

use Livewire\Volt\Component;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Validation\Rule;

new class extends Component {
    public ?Appointment $appointment = null;

    public $patient_id = '';
    public $doctor_id = '';
    public $date = '';
    public $time = '';
    public $status = 'PENDING';
    public $note = '';

    public function mount(?Appointment $appointment = null)
    {
        if ($appointment && $appointment->exists) {
            $this->appointment = $appointment;
            $this->patient_id = $appointment->patient_id;
            $this->doctor_id = $appointment->doctor_id;
            $this->date = $appointment->appt_date;
            $this->time = $appointment->appt_time;
            $this->status = $appointment->appt_status;
            $this->note = $appointment->appt_note;
        } else {
            $this->date = now()->format('Y-m-d');
        }
    }

    public function save()
    {
        $rules = [
            'patient_id' => 'required|exists:patients,patient_id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'note' => 'nullable|string',
        ];

        if ($this->appointment) {
            // Edit mode: Doctor is required
            $rules['doctor_id'] = 'required|exists:doctors,doctor_id';
            $rules['status'] = 'required|string';
        } else {
            // Create mode: Doctor is optional (pool)
            $rules['doctor_id'] = 'nullable|exists:doctors,doctor_id';
            // Status defaults to PENDING
        }

        $this->validate($rules);

        // Simple conflict check (only if doctor is selected)
        if ($this->doctor_id) {
            $exists = Appointment::where('doctor_id', $this->doctor_id)
                ->where('appt_date', $this->date)
                ->where('appt_time', $this->time)
                ->when($this->appointment, function ($q) {
                    return $q->where('appt_id', '!=', $this->appointment->appt_id);
                })
                ->exists();

            if ($exists) {
                $this->addError('time', 'This time slot is already booked for the selected doctor.');
                return;
            }
        }



        $data = [
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'appt_date' => $this->date,
            'appt_time' => $this->time,
            'appt_status' => $this->status,
            'appt_note' => $this->note,
        ];

        if ($this->appointment) {
            $this->appointment->update($data);
            $action = 'update_appointment';
            $description = "Updated appointment for Patient ID: {$this->patient_id}";
        } else {
            Appointment::create($data);
            $action = 'create_appointment';
            $description = "Booked appointment for Patient ID: {$this->patient_id}";
        }

        // Log action
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('appointments.index')
            ->with('status', 'Appointment saved successfully.');
    }

    public function with()
    {
        return [
            'patients' => Patient::orderBy('patient_name')->get(),
            'doctors' => Doctor::orderBy('doctor_name')->get(),
        ];
    }
};
?>

<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <flux:button variant="ghost" href="{{ route('appointments.index') }}" icon="chevron-left" class="-ml-2 mb-4">
            Back to Calendar
        </flux:button>
        <h1 class="text-2xl font-bold text-gray-900">{{ $appointment ? 'Edit Appointment' : 'Book Appointment' }}</h1>
        <p class="text-sm text-gray-600">Schedule a consultation session.</p>
    </div>

    <flux:card>
        <form wire:submit="save" class="space-y-6">

            <flux:select wire:model="patient_id" label="Patient" placeholder="Select Patient" searchable>
                @foreach($patients as $p)
                    <flux:select.option value="{{ $p->patient_id }}">{{ $p->patient_name }} ({{ $p->ic_number }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            @if($appointment)
                <flux:select wire:model="doctor_id" label="Doctor" placeholder="Select Doctor">
                    @foreach($doctors as $d)
                        <flux:select.option value="{{ $d->doctor_id }}">{{ $d->doctor_name }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="date" type="date" label="Date" />
                <flux:select wire:model="time" label="Time Slot">
                    {{-- Generate slots every 30 mins in 12-hour format --}}
                    @foreach(['09:00 AM', '09:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM', '02:00 PM', '02:30 PM', '03:00 PM', '03:30 PM', '04:00 PM', '04:30 PM'] as $slot)
                        <flux:select.option value="{{ $slot }}">{{ $slot }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            @if($appointment)
                <flux:select wire:model="status" label="Status">
                    <flux:select.option value="PENDING">Pending</flux:select.option>
                    <flux:select.option value="CONFIRMED">Confirmed</flux:select.option>
                    <flux:select.option value="Nice">Nice</flux:select.option>
                    <flux:select.option value="CANCELLED">Cancelled</flux:select.option>
                    <flux:select.option value="COMPLETED">Completed</flux:select.option>
                </flux:select>
            @endif

            <flux:textarea wire:model="note" label="Notes" placeholder="Reason for visit..." />

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <flux:button href="{{ route('appointments.index') }}" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $appointment ? 'Update Appointment' : 'Confirm Booking' }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>