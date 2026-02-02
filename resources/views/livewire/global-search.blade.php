<?php

use Livewire\Volt\Component;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Medication;

new class extends Component {
    public $search = '';
    public $results = [];
    public $debug = '';

    public function performSearch()
    {
        $term = trim((string) $this->search);

        if (mb_strlen($term) < 2) {
            $this->results = [];
            return;
        }

        $patients = Patient::query()
            ->whereRaw('LOWER(patient_name) LIKE ?', ['%' . mb_strtolower($term) . '%'])
            ->orWhereRaw('LOWER(student_id) LIKE ?', ['%' . mb_strtolower($term) . '%'])
            ->orWhereRaw('LOWER(ic_number) LIKE ?', ['%' . mb_strtolower($term) . '%'])
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'type' => 'Patient',
                'title' => $p->patient_name,
                'sub' => $p->student_id ?? $p->ic_number ?? ('ID: ' . $p->patient_id),
                'route' => route('patients.show', $p->patient_id),
            ]);

        $doctors = Doctor::query()
            ->whereRaw('LOWER(doctor_name) LIKE ?', ['%' . mb_strtolower($term) . '%'])
            ->limit(5)
            ->get()
            ->map(fn($d) => [
                'type' => 'Doctor',
                'title' => $d->doctor_name,
                'sub' => 'ID: ' . $d->doctor_id,
                'route' => '#',
            ]);

        $meds = Medication::query()
            ->whereRaw('LOWER(meds_name) LIKE ?', ['%' . mb_strtolower($term) . '%'])
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'type' => 'Medication',
                'title' => $m->meds_name,
                'sub' => $m->meds_type ?? 'Medication',
                'route' => route('inventory.mex.index'),
            ]);

        $this->results = $patients->concat($doctors)->concat($meds)->toArray();
    }

    public function testClick()
    {
        $this->debug = 'Livewire click works at ' . now()->format('H:i:s');
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

            <form wire:submit.prevent="performSearch" class="flex items-stretch gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <flux:icon.magnifying-glass variant="outline" class="w-4 h-4 text-zinc-400" />
                    </div>
                    <input
                        type="text"
                        wire:model.defer="search"
                        placeholder="Type to search..."
                        autofocus
                        class="w-full pl-10 pr-3 py-2 text-sm bg-white border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                    />
                </div>
                <flux:button type="submit" variant="primary" class="whitespace-nowrap" wire:click="performSearch">
                    Search
                </flux:button>
            </form>
            @if($debug)
                <div class="text-xs text-green-600 bg-green-50 p-2 rounded">
                    {{ $debug }}
                </div>
            @endif
            <flux:button wire:click="testClick" variant="ghost" size="sm">Test Livewire Click</flux:button>

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