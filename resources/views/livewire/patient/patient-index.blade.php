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
            'patients' => $query->latest('patient_id')->paginate(10),
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

    <div class="flex gap-4 mb-6">
        <div class="flex-1">
            <flux:input wire:model.live="search" placeholder="Search by Name, IC, or Student ID..."
                icon="magnifying-glass" />
        </div>
    </div>

    <div class="px-4 bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-4 pl-12 pr-3 text-left text-sm font-semibold text-gray-900">Name
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Identity
                        (IC/Student)</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Type</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Contact</th>
                    <th scope="col" class="relative py-4 pl-3 pr-12">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($patients as $patient)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-12 pr-3 text-sm">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-900">{{ $patient->patient_name }}</span>
                                <span class="text-xs text-gray-500">
                                    {{ $patient->patient_gender === 'M' ? 'Male' : 'Female' }} •
                                    {{ \Carbon\Carbon::parse($patient->patient_dob)->age }} y/o
                                </span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            <div class="flex flex-col">
                                <span class="font-mono text-xs text-gray-900">{{ $patient->ic_number }}</span>
                                <span class="text-xs text-gray-500">{{ $patient->student_id }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $patient->patient_type === 'STAFF' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $patient->patient_type ?? 'STUDENT' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $patient->patient_hp }}</td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-12 text-right text-sm font-medium">
                            <flux:button variant="ghost" size="sm" icon="eye"
                                href="{{ route('patients.show', $patient->patient_id) }}" tooltip="View Profile" />
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                href="{{ route('patients.edit', $patient->patient_id) }}" tooltip="Edit Details" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 sm:px-6">
            {{ $patients->links() }}
        </div>
    </div>
</div>