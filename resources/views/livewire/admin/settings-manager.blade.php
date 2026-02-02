<?php

use Livewire\Volt\Component;
use App\Models\Setting;

new class extends Component {
    public $clinic_name = '';
    public $operating_hours = '';
    public $maintenance_mode = false;

    public function mount()
    {
        $this->clinic_name = Setting::where('key', 'clinic_name')->first()?->value ?? 'UK UiTM Management System';
        $this->operating_hours = Setting::where('key', 'operating_hours')->first()?->value ?? '8:00 AM - 5:00 PM';
        
        // Check maintenance mode from file first, then fallback to database
        $maintenanceFile = storage_path('framework/maintenance.json');
        if (file_exists($maintenanceFile)) {
            $this->maintenance_mode = true;
        } else {
            $this->maintenance_mode = (bool) (Setting::where('key', 'maintenance_mode')->first()?->value ?? false);
        }
    }

    public function save()
    {
        $this->validate([
            'clinic_name' => 'required|string|max:255',
            'operating_hours' => 'required|string|max:255',
        ]);

        Setting::updateOrCreate(['key' => 'clinic_name'], ['value' => $this->clinic_name, 'type' => 'string']);
        Setting::updateOrCreate(['key' => 'operating_hours'], ['value' => $this->operating_hours, 'type' => 'string']);
        Setting::updateOrCreate(['key' => 'maintenance_mode'], ['value' => $this->maintenance_mode, 'type' => 'boolean']);

        // Update maintenance mode file
        $maintenanceFile = storage_path('framework/maintenance.json');
        if ($this->maintenance_mode) {
            file_put_contents($maintenanceFile, json_encode(['enabled' => true, 'time' => now()->timestamp]));
        } else {
            if (file_exists($maintenanceFile)) {
                unlink($maintenanceFile);
            }
        }

        // Log action
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_settings',
            'description' => "Updated global system settings",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        \Flux::toast('Settings updated successfully.');
    }
};
?>

<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">System Settings</h1>
        <p class="text-sm text-gray-600">Configure global variables for the clinic.</p>
    </div>

    <flux:card>
        <form wire:submit="save" class="space-y-6">
            <flux:input wire:model="clinic_name" label="Clinic Name" placeholder="e.g. UK UiTM Management System" />

            <flux:input wire:model="operating_hours" label="Operating Hours" placeholder="e.g. 8:00 AM - 5:00 PM" />

            <flux:field>
                <flux:label>Maintenance Mode</flux:label>
                <flux:description>If enabled, non-admin users will be restricted from using the system.
                </flux:description>
                <flux:switch wire:model="maintenance_mode" />
            </flux:field>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <flux:button type="submit" variant="primary">Save Settings</flux:button>
            </div>
        </form>
    </flux:card>
</div>