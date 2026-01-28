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
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
        <p class="text-sm text-gray-600">Track all administrative actions and security events.</p>
    </div>

    <div class="px-4 bg-white overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-4 pl-12 pr-3 text-left text-sm font-semibold text-gray-900">Timestamp</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">User</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Action</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Description</th>
                    <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-900">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-12 pr-3 text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            @if($log->user)
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ $log->user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $log->user->email }}</span>
                                </div>
                            @else
                                <span class="text-gray-500 italic text-xs">System / Unknown</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 text-zinc-800">
                                {{ strtoupper(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $log->description }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-xs font-mono text-gray-500">{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 sm:px-6">
            {{ $logs->links() }}
        </div>
    </div>
</div>