<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Patient::query();

        if ($this->search) {
            $query->where('patient_name', 'like', '%' . $this->search . '%')
                ->orWhere('ic_number', 'like', '%' . $this->search . '%')
                ->orWhere('student_id', 'like', '%' . $this->search . '%');
        }

        return [
            'patients' => $query->latest('patient_ID')->paginate(10),
        ];
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Patient Repository</h1>
            <p class="text-sm text-gray-600">Manage patient records and medical history.</p>
        </div>
        <div class="flex gap-2">
            <flux:button icon="plus" variant="primary" href="{{ route('patients.create') }}">
                Register Patient
            </flux:button>
        </div>
    </div>

    <div class="flex gap-4">
        <div class="flex-1">
            <flux:input wire:model.live="search" placeholder="Search by Name, IC, or Student ID..."
                icon="magnifying-glass" />
        </div>
    </div>

    <flux:card class="overflow-hidden">
        <flux:table :paginate="$patients">
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Identity (IC/Student)</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Contact</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($patients as $patient)
                    <flux:table.row :key="$patient->patient_ID">
                        <flux:table.cell class="font-medium">
                            <div class="flex flex-col">
                                <span>{{ $patient->patient_name }}</span>
                                <span
                                    class="text-xs text-gray-600">{{ $patient->patient_gender === 'M' ? 'Male' : 'Female' }}
                                    • {{ \Carbon\Carbon::parse($patient->patient_DOB)->age }} y/o</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-mono text-xs">{{ $patient->ic_number }}</span>
                                <span class="text-xs text-gray-600">{{ $patient->student_id }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $patient->patient_type === 'STAFF' ? 'purple' : 'blue' }}" size="sm">
                                {{ $patient->patient_type ?? 'STUDENT' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $patient->patient_HP }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button variant="ghost" size="sm" icon="eye"
                                href="{{ route('patients.show', $patient->patient_ID) }}" tooltip="View Profile" />
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                href="{{ route('patients.edit', $patient->patient_ID) }}" tooltip="Edit Details" />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>