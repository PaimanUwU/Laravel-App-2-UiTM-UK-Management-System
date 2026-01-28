<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Medication;

new class extends Component {
    public $search = '';
    public $results = [];

    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->results = [];
            return;
        }

        $patients = Patient::where('student_id', 'like', "%{$this->search}%")
            ->orWhere('ic_number', 'like', "%{$this->search}%")
            ->orWhere('patient_name', 'like', "%{$this->search}%")
            ->take(5)
            ->get()
            ->map(fn($p) => ['type' => 'Patient', 'title' => $p->patient_name, 'sub' => $p->student_id ?? $p->ic_number, 'route' => '#']); // Route placeholder

        $doctors = Doctor::where('doctor_name', 'like', "%{$this->search}%")
            ->take(5)
            ->get()
            ->map(fn($d) => ['type' => 'Doctor', 'title' => $d->doctor_name, 'sub' => 'ID: ' . $d->doctor_ID, 'route' => '#']);

        $meds = Medication::where('meds_name', 'like', "%{$this->search}%")
            ->take(5)
            ->get()
            ->map(fn($m) => ['type' => 'Medication', 'title' => $m->meds_name, 'sub' => $m->stock_quantity . ' in stock', 'route' => route('inventory.mex.index')]);

        $this->results = $patients->concat($doctors)->concat($meds)->toArray();
    }
};
?>

<div class="relative w-full max-w-lg">
    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
        placeholder="Search patients, doctors, or meds..."
        class="bg-gray-50 border-none shadow-none focus:ring-2 focus:ring-teal-500 rounded-full" />

    @if(!empty($results))
        <div class="absolute w-full mt-2 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-50">
            <div class="max-h-96 overflow-y-auto">
                @foreach($results as $result)
                    <a href="{{ $result['route'] }}"
                        class="flex items-center gap-4 p-4 hover:bg-gray-50 border-b border-gray-50 last:border-0 transition-colors">
                        <div class="flex-shrink-0">
                            @if($result['type'] === 'Patient')
                                <flux:icon.user variant="outline" class="w-8 h-8 text-teal-600 bg-teal-50 p-1.5 rounded-lg" />
                            @elseif($result['type'] === 'Doctor')
                                <flux:icon.briefcase variant="outline" class="w-8 h-8 text-blue-600 bg-blue-50 p-1.5 rounded-lg" />
                            @else
                                <flux:icon.beaker variant="outline" class="w-8 h-8 text-purple-600 bg-purple-50 p-1.5 rounded-lg" />
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-900">{{ $result['title'] }}</div>
                            <div class="text-xs text-gray-600">{{ $result['sub'] }}</div>
                        </div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-600 bg-gray-100 px-2 py-1 rounded">
                            {{ $result['type'] }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>