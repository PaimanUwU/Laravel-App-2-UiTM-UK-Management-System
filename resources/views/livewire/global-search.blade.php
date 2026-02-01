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
            ->map(fn($p) => ['type' => 'Patient', 'title' => $p->patient_name, 'sub' => $p->student_id ?? $p->ic_number, 'route' => route('patients.show', $p->patient_id)]);

        $doctors = Doctor::where('doctor_name', 'like', "%{$this->search}%")
            ->take(5)
            ->get()
            ->map(fn($d) => ['type' => 'Doctor', 'title' => $d->doctor_name, 'sub' => 'ID: ' . $d->doctor_id, 'route' => '#']);

        $meds = Medication::where('meds_name', 'like', "%{$this->search}%")
            ->take(5)
            ->get()
            ->map(fn($m) => ['type' => 'Medication', 'title' => $m->meds_name, 'sub' => $m->stock_quantity . ' in stock', 'route' => route('inventory.mex.index')]);

        $this->results = $patients->concat($doctors)->concat($meds)->toArray();
    }
};
?>

<div x-data @keydown.window.prevent.slash="document.getElementById('global-search-trigger').click()">
    <flux:modal.trigger name="global-search-modal">
        <button id="global-search-trigger" type="button"
            class="w-full flex items-center px-3 py-2 text-sm text-zinc-500 bg-zinc-50 border border-zinc-200 rounded-lg hover:bg-zinc-100 transition-colors group">
            <flux:icon.magnifying-glass variant="outline"
                class="w-4 h-4 mr-2 text-zinc-400 group-hover:text-zinc-600" />
            <span>Search...</span>
            <kbd
                class="ml-auto flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-sans font-medium text-zinc-400 bg-white border border-zinc-200 rounded shadow-sm group-hover:bg-zinc-50">
                <span class="text-xs">/</span>
            </kbd>
        </button>
    </flux:modal.trigger>

    <flux:modal name="global-search-modal" class="md:w-[40rem] w-full">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Global Search</flux:heading>
                <flux:subheading>Search for patients, doctors, or medications.</flux:subheading>
            </div>

            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Type to search..."
                autofocus />

            <div class="max-h-[60vh] overflow-y-auto space-y-2 p-1">
                @if(!empty($results))
                    @foreach($results as $result)
                        <a href="{{ $result['route'] }}"
                            class="flex items-center gap-4 p-3 hover:bg-zinc-50 rounded-lg border border-transparent hover:border-zinc-100 transition-colors group">
                            <div class="flex-shrink-0">
                                @if($result['type'] === 'Patient')
                                    <flux:icon.user variant="outline"
                                        class="w-8 h-8 text-accent bg-accent/10 p-1.5 rounded-lg group-hover:bg-white transition-all" />
                                @elseif($result['type'] === 'Doctor')
                                    <flux:icon.briefcase variant="outline"
                                        class="w-8 h-8 text-zinc-600 bg-zinc-100 p-1.5 rounded-lg group-hover:bg-white transition-all" />
                                @else
                                    <flux:icon.beaker variant="outline"
                                        class="w-8 h-8 text-zinc-600 bg-zinc-100 p-1.5 rounded-lg group-hover:bg-white transition-all" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-zinc-900 truncate">{{ $result['title'] }}</div>
                                <div class="text-xs text-zinc-500 truncate">{{ $result['sub'] }}</div>
                            </div>
                            <div class="flex-shrink-0">
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 bg-zinc-100 px-2 py-0.5 rounded">
                                    {{ $result['type'] }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                @elseif(strlen($search) >= 2)
                    <div class="flex flex-col items-center justify-center py-12 text-center text-zinc-400">
                        <flux:icon.magnifying-glass variant="outline" class="w-12 h-12 mb-3" />
                        <p class="text-sm">No results found for "{{ $search }}"</p>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center text-zinc-400">
                        <p class="text-sm">Start typing to see results...</p>
                    </div>
                @endif
            </div>
        </div>
    </flux:modal>
</div>