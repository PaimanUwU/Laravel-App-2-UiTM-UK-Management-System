<?php

use Livewire\Volt\Component;
use App\Models\Medication;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[\Livewire\Attributes\On('medication-updated')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Medication::query();

        if ($this->search) {
            $query->where('meds_name', 'like', '%' . $this->search . '%')
                  ->orWhere('meds_type', 'like', '%' . $this->search . '%');
        }

        return [
            'medications' => $query->latest('meds_ID')->paginate(10),
        ];
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Medication Inventory</h1>
            <p class="text-sm text-gray-600">View and manage drug stock levels.</p>
        </div>
        <div class="flex gap-2">
            <flux:button icon="plus" variant="primary" x-on:click="$dispatch('open-modal', 'create-medication')">
                Add Medication
            </flux:button>
        </div>
    </div>

    <div class="flex gap-4">
        <div class="flex-1">
            <flux:input wire:model.live="search" placeholder="Search medications..." icon="magnifying-glass" />
        </div>
    </div>

    <flux:card class="overflow-hidden">
        <flux:table :paginate="$medications">
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Stock Level</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($medications as $med)
                    <flux:table.row :key="$med->meds_ID">
                        <flux:table.cell class="font-medium">{{ $med->meds_name }}</flux:table.cell>
                        <flux:table.cell>{{ $med->meds_type }}</flux:table.cell>
                        <flux:table.cell>{{ $med->stock_quantity }} {{ $med->meds_type ?? 'units' }}</flux:table.cell>
                        <flux:table.cell>
                            @if($med->stock_quantity <= 0)
                                <flux:badge color="red" size="sm">Out of Stock</flux:badge>
                            @elseif($med->stock_quantity <= $med->min_threshold)
                                <flux:badge color="orange" size="sm">Low Stock</flux:badge>
                            @else
                                <flux:badge color="green" size="sm">Healthy</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button variant="ghost" size="sm" icon="plus-circle" tooltip="Adjust Stock" 
                                x-on:click="$dispatch('setAdjustmentMed', { id: {{ $med->meds_ID }} }); $dispatch('open-modal', 'adjust-stock')" />
                            <flux:button variant="ghost" size="sm" icon="pencil-square" 
                                x-on:click="$dispatch('setEditMed', { id: {{ $med->meds_ID }} }); $dispatch('open-modal', 'edit-medication')" />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <livewire:inventory.stock-adjustment />
    <livewire:inventory.medication-form />
</div>