<?php

use Livewire\Volt\Component;
use App\Models\Medication;
use Livewire\WithPagination;

new class extends Component {
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
            'medications' => $query->latest('meds_id')->paginate(10),
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

    <div class="flex gap-4 mb-6">
        <div class="flex-1">
            <flux:input wire:model.live="search" placeholder="Search medications..." icon="magnifying-glass" />
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-4 pl-12 pr-3 text-left text-sm font-semibold text-gray-900">Name
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Type</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Stock Level</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th scope="col" class="relative py-4 pl-3 pr-12">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($medications as $med)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-12 pr-3 text-sm font-medium text-gray-900">
                            {{ $med->meds_name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $med->meds_type }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $med->stock_quantity }}
                            {{ $med->meds_type ?? 'units' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            @if($med->stock_quantity <= 0)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Out of Stock
                                </span>
                            @elseif($med->stock_quantity <= $med->min_threshold)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                    Low Stock
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Healthy
                                </span>
                            @endif
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-12 text-right text-sm font-medium">
                            <flux:button variant="ghost" size="sm" icon="plus-circle" tooltip="Adjust Stock"
                                x-on:click="$dispatch('setAdjustmentMed', { id: {{ $med->meds_id }} }); $dispatch('open-modal', 'adjust-stock')" />
                            <flux:button variant="ghost" size="sm" icon="pencil-square"
                                x-on:click="$dispatch('setEditMed', { id: {{ $med->meds_id }} }); $dispatch('open-modal', 'edit-medication')" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 sm:px-6">
            {{ $medications->links() }}
        </div>
    </div>

    <livewire:inventory.stock-adjustment />
    <livewire:inventory.medication-form />
</div>
