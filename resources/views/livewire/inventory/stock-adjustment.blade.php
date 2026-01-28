<?php

use Livewire\Volt\Component;
use App\Models\Medication;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $meds_id;
    public $quantity = 0;
    public $type = 'IN';
    public $reason = '';

    #[\Livewire\Attributes\On('setAdjustmentMed')]
    public function setMed($id)
    {
        $this->meds_id = $id;
    }

    public function adjust()
    {
        $this->validate([
            'meds_id' => 'required|exists:medications,meds_id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:IN,OUT',
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () {
            $med = Medication::find($this->meds_id);

            // Create movement
            StockMovement::create([
                'meds_id' => $this->meds_id,
                'quantity' => $this->quantity,
                'type' => $this->type,
                'reason' => $this->reason,
                'user_id' => auth()->id(),
            ]);

            // Update stock level
            if ($this->type === 'IN') {
                $med->increment('stock_quantity', $this->quantity);
            } else {
                $med->decrement('stock_quantity', $this->quantity);
            }

            // Log action to audit logs
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'stock_adjustment',
                'description' => "Adjusted stock for {$med->meds_name}: {$this->type} {$this->quantity} ({$this->reason})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        $this->reset(['quantity', 'reason']);
        $this->dispatch('close-modal', 'adjust-stock');
        $this->dispatch('medication-updated');
        flux()->toast('Stock adjusted successfully.');
    }
};
?>

<div>
    <flux:modal name="adjust-stock" class="max-w-md">
        <form wire:submit="adjust" class="space-y-6">
            <div>
                <h1 class="text-lg font-bold">Adjust Stock</h1>
                <p class="text-sm text-gray-600">Record a stock movement for this medication.</p>
            </div>

            <flux:radio.group wire:model="type" label="Movement Type">
                <flux:radio value="IN" label="Restock (IN)" />
                <flux:radio value="OUT" label="Removal (OUT)" />
            </flux:radio.group>

            <flux:input wire:model="quantity" type="number" label="Quantity" placeholder="e.g. 50" />

            <flux:input wire:model="reason" label="Reason" placeholder="e.g. Monthly Restock, Expired, etc." />

            <div class="flex gap-3 justify-end pt-4">
                <flux:button x-on:click="$dispatch('close-modal', 'adjust-stock')" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Confirm Adjustment</flux:button>
            </div>
        </form>
    </flux:modal>
</div>