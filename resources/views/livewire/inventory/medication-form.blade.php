<?php

use Livewire\Volt\Component;
use App\Models\Medication;

new class extends Component {
    public ?Medication $medication = null;
    public $name = '';
    public $type = '';
    public $min_threshold = 10;


    #[\Livewire\Attributes\On('setEditMed')]
    public function setEditMed($id)
    {
        $this->medication = Medication::find($id);
        $this->name = $this->medication->meds_name;
        $this->type = $this->medication->meds_type;
        $this->min_threshold = $this->medication->min_threshold;
    }

    #[\Livewire\Attributes\On('reset-medication-form')]
    public function resetForm()
    {
        $this->reset(['name', 'type', 'min_threshold', 'medication']);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'min_threshold' => 'required|integer|min:0',
        ]);

        if ($this->medication) {
            $this->medication->update([
                'meds_name' => $this->name,
                'meds_type' => $this->type,
                'min_threshold' => $this->min_threshold,
            ]);
            $action = 'update_medication';
            $description = "Updated medication: {$this->name}";
        } else {
            Medication::create([
                'meds_name' => $this->name,
                'meds_type' => $this->type,
                'min_threshold' => $this->min_threshold,
                'stock_quantity' => 0, // Initial stock
            ]);
            $action = 'create_medication';
            $description = "Created new medication: {$this->name}";
        }

        // Log action
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->reset(['name', 'type', 'min_threshold', 'medication']);
        $this->dispatch('close-modal', 'create-medication');
        $this->dispatch('close-modal', 'edit-medication');
        $this->dispatch('medication-updated');
        \Flux::toast('Medication saved successfully.');
    }
};
?>

<div>
    <flux:modal name="create-medication" class="max-w-md">
        <form wire:submit="save" class="space-y-6">
            <div>
                <h1 class="text-lg font-bold">Add Medication</h1>
                <p class="text-sm text-gray-600">Create a new entry in the drug registry.</p>
            </div>

            <flux:input wire:model="name" label="Medication Name" placeholder="e.g. Paracetamol" />

            <flux:input wire:model="type" label="Unit/Type" placeholder="e.g. 500mg, Tablet, Bottle" />

            <flux:input wire:model="min_threshold" type="number" label="Low Stock Threshold" />

            <div class="flex gap-3 justify-end pt-4">
                <flux:button x-on:click="$dispatch('close-modal', 'create-medication')" variant="ghost">Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">Save Medication</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="edit-medication" class="max-w-md">
        <form wire:submit="save" class="space-y-6">
            <div>
                <h1 class="text-lg font-bold">Edit Medication</h1>
                <p class="text-sm text-gray-600">Update drug details.</p>
            </div>

            <flux:input wire:model="name" label="Medication Name" />

            <flux:input wire:model="type" label="Unit/Type" />

            <flux:input wire:model="min_threshold" type="number" label="Low Stock Threshold" />

            <div class="flex gap-3 justify-end pt-4">
                <flux:button x-on:click="$dispatch('close-modal', 'edit-medication')" variant="ghost">Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">Update Medication</flux:button>
            </div>
        </form>
    </flux:modal>
</div>