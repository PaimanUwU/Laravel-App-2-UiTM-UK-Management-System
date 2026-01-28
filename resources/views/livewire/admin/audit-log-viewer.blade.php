<?php

use Livewire\Volt\Component;
use App\Models\AuditLog;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [
            'logs' => AuditLog::with('user')->latest()->paginate(20),
        ];
    }
};
?>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
        <p class="text-sm text-gray-600">Track all administrative actions and security events.</p>
    </div>

    <flux:card class="overflow-hidden">
        <flux:table :paginate="$logs">
            <flux:table.columns>
                <flux:table.column>Timestamp</flux:table.column>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Action</flux:table.column>
                <flux:table.column>Description</flux:table.column>
                <flux:table.column>IP Address</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($logs as $log)
                    <flux:table.row :key="$log->id">
                        <flux:table.cell class="whitespace-nowrap text-xs text-gray-600">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($log->user)
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $log->user->name }}</span>
                                    <span class="text-xs text-gray-600">{{ $log->user->email }}</span>
                                </div>
                            @else
                                <span class="text-gray-600 font-italic">System / Unknown</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="zinc" size="sm">
                                {{ strtoupper(str_replace('_', ' ', $log->action)) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">{{ $log->description }}</flux:table.cell>
                        <flux:table.cell class="text-xs font-mono">{{ $log->ip_address }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>